<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Service;

use MoorlFoundation\Core\System\PriceCalculatorInterface;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection as CalculatedPriceCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\ReferencePriceDefinition;
use Shopware\Core\Content\Product\DataAbstractionLayer\CheapestPrice\CalculatedCheapestPrice;
use Shopware\Core\Content\Product\SalesChannel\Price\AbstractProductPriceCalculator;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\Price;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use Shopware\Core\Framework\Struct\Struct;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class PriceCalculatorService
{
    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_FIXED = 'fixed';
    public const SOURCE_ORIGIN_LIST_PRICE = 'origin-list-price';
    public const SOURCE_ORIGIN_PRICE = 'origin-price';
    public const CONFIG_KEY_PRIORITY = 'priceCalculatorPriority';
    public const CONFIG_KEY_SHOULD_BREAK = 'priceCalculatorShouldBreak';

    private array $calculatedPricesCache = [];
    /**
     * @param PriceCalculatorInterface[] $collectedCalculators
     */
    private array $collectedCalculators = [];

    /**
     * @param PriceCalculatorInterface[] $calculators
     */
    public function __construct(
        private readonly QuantityPriceCalculator $quantityPriceCalculator,
        private readonly SystemConfigService $systemConfigService,
        protected iterable $calculators
    )
    {
    }

    public function calculate(iterable $products, SalesChannelContext $salesChannelContext): void
    {
        foreach ($products as $product) {
            $this->collect($product, $salesChannelContext);
            $this->process($product, $salesChannelContext);
        }
    }

    private function collect(SalesChannelProductEntity $product, SalesChannelContext $salesChannelContext): void
    {
        $collectedCalculators = [];

        /** @var PriceCalculatorInterface $calculator */
        foreach ($this->calculators as $calculator) {
            $calculator->init($salesChannelContext);

            if ($calculator->match($product, $salesChannelContext)) {
                $collectedCalculators[] = $calculator;
            }
        }

        uasort($collectedCalculators, fn(PriceCalculatorInterface $a, PriceCalculatorInterface $b) => $b->getPriority() <=> $a->getPriority());

        $this->collectedCalculators = $collectedCalculators;
    }

    private function process(SalesChannelProductEntity $product, SalesChannelContext $salesChannelContext): void
    {
        /** @var PriceCalculatorInterface $calculator */
        foreach ($this->collectedCalculators as $calculator) {
            $calculator->calculate($product, $salesChannelContext);

            if ($calculator->shouldBreak()) {
                return;
            }
        }
    }

    public function setFixedPrice(
        string $initiator,
        SalesChannelContext $salesChannelContext,
        SalesChannelProductEntity $product,
        ?PriceCollection $prices,
        bool $showDiscount = true,
        string $listPriceSource = self::SOURCE_ORIGIN_LIST_PRICE,
        ?Struct $extension = null
    ): void
    {
        if (!$prices) {
            return;
        }

        if ($this->shouldSkip($initiator, $product)) {
            return;
        }

        if ($extension) {
            $product->addExtension($initiator, $extension);
        }

        $calculated = $this->getCalculatedPrice(
            $salesChannelContext,
            $prices,
            $product->getTaxId()
        );

        $factor = $calculated->getUnitPrice() / $product->getCalculatedPrice()->getUnitPrice();

        $this->calculatePriceByFactor(
            $initiator,
            $salesChannelContext,
            $product,
            $factor,
            $showDiscount,
            $listPriceSource
        );
    }

    public function calculatePriceByFactor(
        string $initiator,
        SalesChannelContext $salesChannelContext,
        SalesChannelProductEntity $product,
        float $factor = 1,
        bool $showDiscount = true,
        string $listPriceSource = self::SOURCE_ORIGIN_LIST_PRICE,
        string $calculationPriceSource = self::SOURCE_ORIGIN_PRICE,
        ?Struct $extension = null
    ): void
    {
        /* Factor 1 = original price */
        if ($factor == 1) {
            return;
        }

        if ($this->shouldSkip($initiator, $product)) {
            return;
        }

        if ($extension) {
            $product->addExtension($initiator, $extension);
        }

        $product->assign([$initiator => $factor]);

        /* Handle price */
        $this->cacheAndAssignMainPrice($initiator, $product, $this->calculateItem(
            $salesChannelContext,
            $product,
            $product->getCalculatedPrice(),
            $factor,
            $showDiscount,
            $listPriceSource,
            $calculationPriceSource
        ));

        /* Handle the cheapest price */
        $product->setCalculatedCheapestPrice(CalculatedCheapestPrice::createFrom($this->calculateItem(
            $salesChannelContext,
            $product,
            $product->getCalculatedCheapestPrice(),
            $factor,
            $showDiscount,
            $listPriceSource,
            $calculationPriceSource
        )));

        /* Handle advanced prices */
        $calculated = new CalculatedPriceCollection();
        foreach ($product->getCalculatedPrices() as $price) {
            $calculated->add($this->calculateItem(
                $salesChannelContext,
                $product,
                $price,
                $factor,
                $showDiscount,
                $listPriceSource,
                $calculationPriceSource
            ));
        }
        $product->assign(['calculatedPrices' => $calculated]);
    }

    private function shouldSkip(string $initiator, SalesChannelProductEntity $product): bool
    {
        if (
            $product->has(self::CONFIG_KEY_SHOULD_BREAK) &&
            $product->get(self::CONFIG_KEY_SHOULD_BREAK) !== $initiator
        ) {
            return true;
        }

        $price = $product->getCalculatedPrice();
        $cheapest = $product->getCalculatedCheapestPrice();

        if ($price->getUnitPrice() == 0 || $cheapest->getUnitPrice() == 0) return true;

        $cached = $this->calculatedPricesCache[$initiator][$product->getId()] ?? null;
        return $cached?->getUnitPrice() === $price->getUnitPrice();
    }

    private function calculateItem(
        SalesChannelContext $salesChannelContext,
        SalesChannelProductEntity $product,
        CalculatedPrice $price,
        float $factor = 1,
        bool $showDiscount = true,
        string $listPriceSource = self::SOURCE_ORIGIN_LIST_PRICE,
        string $calculationPriceSource = self::SOURCE_ORIGIN_PRICE
    ): CalculatedPrice
    {
        $discount = $price->getUnitPrice() * $factor;
        if ($price->getListPrice() && $calculationPriceSource === self::SOURCE_ORIGIN_LIST_PRICE) {
            $discount = $price->getListPrice()->getPrice() * $factor;
        }

        $definition = new QuantityPriceDefinition(
            $discount,
            $salesChannelContext->buildTaxRules($product->getTaxId()),
            $price->getQuantity()
        );

        if ($price->getReferencePrice() !== null) {
            $definition->setReferencePriceDefinition(
                new ReferencePriceDefinition(
                    $price->getReferencePrice()->getPurchaseUnit(),
                    $price->getReferencePrice()->getReferenceUnit(),
                    $price->getReferencePrice()->getUnitName()
                )
            );
        }

        if ($showDiscount && $factor < 1) {
            if ($price->getListPrice() && $listPriceSource === self::SOURCE_ORIGIN_LIST_PRICE) {
                $definition->setListPrice($price->getListPrice()->getPrice());
            } elseif (!$price->getListPrice() || $listPriceSource === self::SOURCE_ORIGIN_PRICE) {
                $definition->setListPrice($price->getUnitPrice());
            }
        }

        if ($price->getRegulationPrice()) {
            $definition->setRegulationPrice($price->getRegulationPrice()->getPrice());
        }

        return $this->quantityPriceCalculator->calculate($definition, $salesChannelContext);
    }

    private function cacheAndAssignMainPrice(
        string $initiator,
        SalesChannelProductEntity $product,
        CalculatedPrice $calculated
    ): void
    {
        $product->setCalculatedPrice($calculated);

        if (!isset($this->calculatedPricesCache[$initiator])) {
            $this->calculatedPricesCache[$initiator] = [];
        }
        $this->calculatedPricesCache[$initiator][$product->getId()] = $calculated;

        $shouldBreak = $this->systemConfigService->getBool(
            sprintf("%s.config.%s", $initiator, self::CONFIG_KEY_SHOULD_BREAK)
        );
        if ($shouldBreak) {
            $product->assign([self::CONFIG_KEY_SHOULD_BREAK => $initiator]);
        }
    }

    private function getCalculatedPrice(SalesChannelContext $context, PriceCollection $prices, string $taxId, int $quantity = 1): CalculatedPrice
    {
        return $this->quantityPriceCalculator->calculate($this->buildDefinition($taxId, $prices, $context, $quantity), $context);
    }

    private function buildDefinition(string $taxId, PriceCollection $prices, SalesChannelContext $context, int $quantity = 1): QuantityPriceDefinition
    {
        return new QuantityPriceDefinition($this->getPriceValue($prices, $context), $context->buildTaxRules($taxId), $quantity);
    }

    private function getPriceValue(PriceCollection $price, SalesChannelContext $context): float
    {
        /** @var Price $currency */
        $currency = $price->getCurrencyPrice($context->getCurrencyId());

        $value = $this->getPriceForTaxState($currency, $context);

        if ($currency->getCurrencyId() !== $context->getCurrency()->getId()) {
            $value *= $context->getContext()->getCurrencyFactor();
        }

        return $value;
    }

    private function getPriceForTaxState(Price $price, SalesChannelContext $context): float
    {
        if ($context->getTaxState() === CartPrice::TAX_STATE_GROSS) {
            return $price->getGross();
        }

        return $price->getNet();
    }
}
