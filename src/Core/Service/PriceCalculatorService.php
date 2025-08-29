<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Service;

use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection as CalculatedPriceCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\ReferencePriceDefinition;
use Shopware\Core\Content\Product\DataAbstractionLayer\CheapestPrice\CalculatedCheapestPrice;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\Price;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class PriceCalculatorService
{
    private array $calculatedPricesCache = [];

    public function __construct(private readonly QuantityPriceCalculator $quantityPriceCalculator)
    {
    }

    public function calculatePriceBySurcharge(
        SalesChannelContext $salesChannelContext,
        SalesChannelProductEntity $product,
        PriceCollection $prices
    ): void
    {
        if ($this->shouldSkip($product)) {
            return;
        }

        $price = $product->getCalculatedPrice();

        $calculated = $this->getCalculatedPrice(
            $salesChannelContext,
            $prices,
            $product->getTaxId(),
            $price->getQuantity()
        );

        $this->cacheAndAssignMainPrice($product, $calculated);
    }

    public function calculatePriceByFactor(
        SalesChannelContext $salesChannelContext,
        SalesChannelProductEntity $product,
        float $factor = 1,
        bool $showDiscount = true
    ): void
    {
        if ($this->shouldSkip($product)) {
            return;
        }

        /* Handle price */
        $this->cacheAndAssignMainPrice($product, $this->calculate(
            $salesChannelContext,
            $product,
            $product->getCalculatedPrice(),
            $factor,
            $showDiscount
        ));

        /* Handle the cheapest price */
        $product->setCalculatedCheapestPrice(CalculatedCheapestPrice::createFrom($this->calculate(
            $salesChannelContext,
            $product,
            $product->getCalculatedCheapestPrice(),
            $factor,
            $showDiscount
        )));

        /* Handle advanced prices */
        $calculated = new CalculatedPriceCollection();

        foreach ($product->getCalculatedPrices() as $price) {
            $calculated->add($this->calculate(
                $salesChannelContext,
                $product,
                $price,
                $factor,
                $showDiscount
            ));
        }

        $product->assign(['calculatedPrices' => $calculated]);
    }

    private function shouldSkip(SalesChannelProductEntity $product): bool
    {
        $price = $product->getCalculatedPrice();
        $cheapest = $product->getCalculatedCheapestPrice();

        if ($price->getUnitPrice() == 0 || $cheapest->getUnitPrice() == 0) return true;

        $cached = $this->calculatedPricesCache[$product->getId()] ?? null;
        return $cached?->getUnitPrice() === $price->getUnitPrice();
    }

    private function calculate(
        SalesChannelContext $salesChannelContext,
        SalesChannelProductEntity $product,
        CalculatedPrice $price,
        float $factor = 1,
        bool $showDiscount = true
    ): CalculatedPrice
    {
        $cheapestPrice = $product->getCalculatedCheapestPrice();

        $discount = $price->getUnitPrice() * $factor;

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

        if (!$cheapestPrice->getListPrice() && $showDiscount && $factor < 1) {
            $definition->setListPrice($cheapestPrice->getUnitPrice());
        } elseif ($cheapestPrice->getListPrice()) {
            $definition->setListPrice($cheapestPrice->getListPrice()->getPrice());
        }

        if ($price->getRegulationPrice()) {
            $definition->setRegulationPrice($price->getRegulationPrice()->getPrice());
        }

        return $this->quantityPriceCalculator->calculate($definition, $salesChannelContext);
    }

    private function cacheAndAssignMainPrice(SalesChannelProductEntity $product, CalculatedPrice $calculated): void
    {
        $product->setCalculatedPrice($calculated);
        $this->calculatedPricesCache[$product->getId()] = $calculated;
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
