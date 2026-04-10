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
use Shopware\Core\Content\Product\ProductEntity;
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
    public const TYPE_NONE = 'none';
    public const ROUNDING_TYPE_NONE = 'none';
    public const ROUNDING_TYPE_DEFAULT = 'default';
    public const ROUNDING_TYPE_FLOOR = 'floor';
    public const ROUNDING_TYPE_CEIL = 'ceil';
    public const SOURCE_ORIGIN_LIST_PRICE = 'origin-list-price';
    public const SOURCE_ORIGIN_PRICE = 'origin-price';
    public const SOURCE_PLUGIN_CONFIG = 'plugin-config';
    public const CONFIG_KEY_ACTIVE = 'priceCalculatorActive';
    public const CONFIG_KEY_PRIORITY = 'priceCalculatorPriority';
    public const CONFIG_KEY_SHOULD_BREAK = 'priceCalculatorShouldBreak';
    public const CONFIG_KEY_CALCULATION_PRICE_SOURCE = 'priceCalculatorCalculationPriceSource';
    public const CONFIG_KEY_LIST_PRICE_SOURCE = 'priceCalculatorListPriceSource';
    public const CONFIG_KEY_ROUNDING_TYPE = 'priceCalculatorRoundingType';
    public const CONFIG_KEY_ROUNDING_STEP = 'priceCalculatorRoundingStep';

    private array $calculatedPricesCache = [];
    /**
     * @param PriceCalculatorInterface[] $collectedCalculators
     */
    private array $collectedCalculators = [];
    private ?array $skippedProductExtensions = null;

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

    public function getPriority(string $initiator, ?string $salesChannelId = null): int
    {
        return $this->systemConfigService->getInt(
            $this->getConfigKey($initiator, self::CONFIG_KEY_PRIORITY),
            $salesChannelId
        );
    }

    public function getShouldBreak(string $initiator, ?string $salesChannelId = null): bool
    {
        return $this->systemConfigService->getBool(
            $this->getConfigKey($initiator, self::CONFIG_KEY_SHOULD_BREAK),
            $salesChannelId
        );
    }

    public function getCalculationPriceSource(string $initiator, string $salesChannelId, ?string $value = null): string
    {
        if ($value && $value !== PriceCalculatorService::SOURCE_PLUGIN_CONFIG) {
            return $value;
        }

        return $this->systemConfigService->get(
            $this->getConfigKey($initiator, self::CONFIG_KEY_CALCULATION_PRICE_SOURCE),
            $salesChannelId
        ) ?? self::SOURCE_ORIGIN_PRICE;
    }

    public function getListPriceSource(string $initiator, string $salesChannelId, ?string $value = null): string
    {
        if ($value && $value !== PriceCalculatorService::SOURCE_PLUGIN_CONFIG) {
            return $value;
        }

        return $this->systemConfigService->get(
            $this->getConfigKey($initiator, self::CONFIG_KEY_LIST_PRICE_SOURCE),
            $salesChannelId
        ) ?? self::SOURCE_ORIGIN_LIST_PRICE;
    }

    public function getRoundingType(string $initiator, string $salesChannelId, ?string $value = null): string
    {
        if ($value && $value !== PriceCalculatorService::SOURCE_PLUGIN_CONFIG) {
            return $value;
        }

        return $this->systemConfigService->get(
            $this->getConfigKey($initiator, self::CONFIG_KEY_ROUNDING_TYPE),
            $salesChannelId
        ) ?? self::ROUNDING_TYPE_NONE;
    }

    public function getRoundingStep(string $initiator, string $salesChannelId, ?float $value = null): float
    {
        if ($value && $value != 0) {
            return $value;
        }

        return $this->systemConfigService->getFloat(
            $this->getConfigKey($initiator, self::CONFIG_KEY_ROUNDING_STEP),
            $salesChannelId
        );
    }

    public function calculate(iterable $products, SalesChannelContext $salesChannelContext): void
    {
        $this->init($salesChannelContext);

        foreach ($products as $product) {
            if (!$product instanceof SalesChannelProductEntity) {
                continue;
            }

            if ($this->isSkippedProduct($product)) {
                continue;
            }

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

    private function init(SalesChannelContext $salesChannelContext): void
    {
        if ($this->skippedProductExtensions !== null) {
            return;
        }

        $list = $this->systemConfigService->getString(
            'MoorlFoundation.config.cmpPriceCalculationSkip',
            $salesChannelContext->getSalesChannelId()
        );

        $list = explode(',', $list);
        $list = array_map('trim', $list);

        $this->skippedProductExtensions = $list;
    }

    private function isSkippedProduct(ProductEntity $product): bool
    {
        foreach ($this->skippedProductExtensions as $skippedProductExtension) {
            if ($product->hasExtension($skippedProductExtension)) {
                return true;
            }
        }

        return false;
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

        if ($this->shouldSkip($initiator, $product, false, false)) {
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

        $price = $product->getCalculatedPrice();
        $cheapest = $product->getCalculatedCheapestPrice();

        if ($price->getUnitPrice() == 0 || $cheapest->getUnitPrice() == 0) {
            $this->cacheAndAssignMainPrice($initiator, $product, $this->calculateItem(
                $salesChannelContext,
                $product,
                $calculated,
                1,
                $showDiscount,
                $listPriceSource
            ));
            return;
        }

        $factor = $calculated->getUnitPrice() / $price->getUnitPrice();

        $this->calculatePriceByFactor(
            initiator: $initiator,
            salesChannelContext: $salesChannelContext,
            product: $product,
            factor: $factor,
            showDiscount: $showDiscount,
            listPriceSource: $listPriceSource,
            useRounding: false
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
        ?Struct $extension = null,
        bool $useRounding = true
    ): void
    {
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

        $roundingType = $useRounding ?
            $this->getRoundingType($initiator, $salesChannelContext->getSalesChannelId()) :
            self::ROUNDING_TYPE_NONE;
        $roundingStep = $useRounding ?
            $this->getRoundingStep($initiator, $salesChannelContext->getSalesChannelId()) :
            0;

        $this->cacheAndAssignMainPrice($initiator, $product, $this->calculateItem(
            $salesChannelContext,
            $product,
            $product->getCalculatedPrice(),
            $factor,
            $showDiscount,
            $listPriceSource,
            $calculationPriceSource,
            $roundingType,
            $roundingStep
        ));

        $product->setCalculatedCheapestPrice(CalculatedCheapestPrice::createFrom($this->calculateItem(
            $salesChannelContext,
            $product,
            $product->getCalculatedCheapestPrice(),
            $factor,
            $showDiscount,
            $listPriceSource,
            $calculationPriceSource,
            $roundingType,
            $roundingStep
        )));

        $calculated = new CalculatedPriceCollection();
        foreach ($product->getCalculatedPrices() as $price) {
            $calculated->add($this->calculateItem(
                $salesChannelContext,
                $product,
                $price,
                $factor,
                $showDiscount,
                $listPriceSource,
                $calculationPriceSource,
                $roundingType,
                $roundingStep
            ));
        }

        $product->assign(['calculatedPrices' => $calculated]);
    }

    private function shouldSkip(
        string $initiator,
        SalesChannelProductEntity $product,
        bool $testBreak = true,
        bool $testZero = true
    ): bool
    {
        if (
            $testBreak &&
            $product->has(self::CONFIG_KEY_SHOULD_BREAK) &&
            $product->get(self::CONFIG_KEY_SHOULD_BREAK) !== $initiator
        ) {
            return true;
        }

        $price = $product->getCalculatedPrice();
        if ($testZero) {
            $cheapest = $product->getCalculatedCheapestPrice();
            if ($price->getUnitPrice() == 0 || $cheapest->getUnitPrice() == 0) {
                return true;
            }
        }

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
        string $calculationPriceSource = self::SOURCE_ORIGIN_PRICE,
        string $roundingType = self::ROUNDING_TYPE_NONE,
        float $roundingStep = 0.00
    ): CalculatedPrice
    {
        $discount = $price->getUnitPrice() * $factor;
        if ($price->getListPrice() && $calculationPriceSource === self::SOURCE_ORIGIN_LIST_PRICE) {
            $discount = $price->getListPrice()->getPrice() * $factor;
        }

        $discount = $this->roundToStep($discount, $roundingStep, $roundingType);

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

        if ($showDiscount && $factor <= 1) {
            $definition->setListPrice($price->getUnitPrice());

            if ($listPriceSource === self::SOURCE_ORIGIN_LIST_PRICE) {
                if ($price->getListPrice()) {
                    $definition->setListPrice($price->getListPrice()->getPrice());
                } elseif ($product->getCalculatedPrice()->getListPrice()) {
                    $definition->setListPrice($product->getCalculatedPrice()->getListPrice()->getPrice());
                }
            }
        }

        if ($price->getRegulationPrice()) {
            $definition->setRegulationPrice($price->getRegulationPrice()->getPrice());
        }

        return $this->quantityPriceCalculator->calculate($definition, $salesChannelContext);
    }

    private function roundToStep(float $price, float $step, string $roundingType): float
    {
        if ($step <= 0) {
            return $price;
        }

        $value = $price / $step;

        return match ($roundingType) {
            self::ROUNDING_TYPE_FLOOR => floor($value) * $step,
            self::ROUNDING_TYPE_CEIL => ceil($value) * $step,
            self::ROUNDING_TYPE_DEFAULT => round($value) * $step,
            default => $price,
        };
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

        if ($this->getShouldBreak($initiator)) {
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

    private function getConfigKey(string $initiator, string $key): string
    {
        return sprintf('%s.config.%s', $initiator, $key);
    }
}
