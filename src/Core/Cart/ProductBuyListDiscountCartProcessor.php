<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Cart;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartDataCollectorInterface;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\PercentagePriceCalculator;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\ReferencePriceDefinition;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotCollection;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Profiling\Profiler;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection;

class ProductBuyListDiscountCartProcessor implements CartProcessorInterface, CartDataCollectorInterface
{
    public function __construct(
        private readonly EntityRepository $cmsSlotRepository,
        private readonly PercentagePriceCalculator $percentagePriceCalculator,
        private readonly QuantityPriceCalculator $quantityPriceCalculator
    )
    {
    }

    public function process(
        CartDataCollection $data,
        Cart $original,
        Cart $toCalculate,
        SalesChannelContext $context,
        CartBehavior $behavior
    ): void
    {
        Profiler::trace('cart::product-buy-list-discount::process', function () use ($data, $toCalculate, $context, $behavior): void {
            $cmsSlots = $this->getCmsSlots($context);
            $discountBundles = $this->discountBundleExtractor($toCalculate, $cmsSlots);
            if (empty($discountBundles)) {
                return;
            }

            foreach ($discountBundles as $discountBundle) {
                $calculated = new PriceCollection();
                $label = [];

                foreach ($toCalculate->getLineItems() as $lineItem) {
                    if (in_array($lineItem->getReferencedId(), $discountBundle['productIds'])) {
                        $calculatedQuantity = $discountBundle['bundleQuantity'] * $discountBundle['productQuantities'][$lineItem->getReferencedId()];

                        $calculated->add(
                            $this->quantityPriceCalculator->calculate(
                                $this->buildPriceDefinition($lineItem->getPrice(), $calculatedQuantity),
                                $context
                            )
                        );
                        $label[] = sprintf("%dx %s", $calculatedQuantity, $lineItem->getLabel());
                    }
                }

                $code = Uuid::randomHex();
                $uniqueKey = 'product-buy-list-discount-' . $code;
                $label = sprintf(
                    "%s%% | %s",
                    $discountBundle['discountPercentage'],
                    $discountBundle['discountName'] ? sprintf("%dx %s", $discountBundle['bundleQuantity'], $discountBundle['discountName']) : implode(', ', $label)
                );

                $item = new LineItem($uniqueKey, LineItem::CUSTOM_LINE_ITEM_TYPE);
                $item->setLabel($label);
                $item->setGood(false);
                $item->setReferencedId($code);
                $item->setPrice($this->percentagePriceCalculator->calculate($discountBundle['discountPercentage'], $calculated, $context));

                $toCalculate->getLineItems()->add($item);
            }
        }, 'cart');
    }

    public function collect(CartDataCollection $data, Cart $original, SalesChannelContext $context, CartBehavior $behavior): void
    {
        Profiler::trace('cart::product-buy-list-discount::collect', function () use ($data, $original, $context, $behavior): void {
        }, 'cart');
    }

    private function getCmsSlots(SalesChannelContext $context): CmsSlotCollection
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('type', ['moorl-product-buy-list', 'moorl-shop-the-look']));
        $criteria->addFilter(new EqualsFilter('config.discountActive.value', "true"));

        return $this->cmsSlotRepository->search($criteria, $context->getContext())->getEntities();
    }

    private function discountBundleExtractor(Cart $cart, CmsSlotCollection $cmsSlots): array
    {
        $discountBundles = [];
        $cartProductQuantities = [];

        foreach ($cart->getLineItems() as $lineItem) {
            $cartProductQuantities[$lineItem->getReferencedId()] = $lineItem->getQuantity();
        }

        /** @var CmsSlotEntity $cmsSlot */
        foreach ($cmsSlots as $cmsSlot) {
            $productQuantities = $this->getProductQuantities($cmsSlot);
            if (count($productQuantities) === 0) {
                continue;
            }

            if (count($productQuantities) > count($cartProductQuantities)) {
                continue;
            }

            $bundleQuantities = array_filter($cartProductQuantities, function($k) use ($productQuantities) {
                return in_array($k, array_keys($productQuantities));
            }, ARRAY_FILTER_USE_KEY);

            if (count($bundleQuantities) !== count($productQuantities)) {
                continue;
            }

            $subtracted = array_map(function ($x, $y) { return (int) floor($x / $y); } , $bundleQuantities, $productQuantities);
            $bundleQuantity = min($subtracted);
            if (!$bundleQuantity) {
                continue;
            }

            foreach (array_keys($productQuantities) as $key) {
                $cartProductQuantities[$key] = $cartProductQuantities[$key] - $bundleQuantity;
            }

            $discountBundles[] = [
                'productIds' => array_keys($productQuantities),
                'productQuantities' => $productQuantities,
                'discountPercentage' => $this->getDiscountPercentage($cmsSlot),
                'discountName' => $this->getDiscountName($cmsSlot),
                'bundleQuantity' => $bundleQuantity,
            ];
        }

        return $discountBundles;
    }

    private function getProductIds(CmsSlotEntity $cmsSlot): array
    {
        $config = $cmsSlot->getFieldConfig()->get('products');
        if ($config && $config->getValue()) {
            return (array) $config->getValue();
        }
        return [];
    }

    private function getDiscountPercentage(CmsSlotEntity $cmsSlot): float
    {
        $config = $cmsSlot->getFieldConfig()->get('discountPercentage');
        if ($config && $config->getValue()) {
            return (float) - $config->getValue();
        }
        return 0;
    }

    private function getDiscountName(CmsSlotEntity $cmsSlot): ?string
    {
        $config = $cmsSlot->getFieldConfig()->get('discountName');
        if ($config && $config->getValue()) {
            return $config->getValue();
        }
        return null;
    }

    private function getProductQuantities(CmsSlotEntity $cmsSlot): array
    {
        $productIds = $this->getProductIds($cmsSlot);
        $productQuantities = [];

        $config = $cmsSlot->getFieldConfig()->get('productQuantities');
        if ($config && $config->getValue()) {
            foreach ((array) $config->getValue() as $key => $value) {
                if (in_array($key, $productIds)) {
                    $productQuantities[$key] = $value;
                }
            }
        }
        return $productQuantities;
    }

    private function buildPriceDefinition(CalculatedPrice $price, int $quantity): QuantityPriceDefinition
    {
        $definition = new QuantityPriceDefinition($price->getUnitPrice(), $price->getTaxRules(), $quantity);
        if ($price->getListPrice() !== null) {
            $definition->setListPrice($price->getListPrice()->getPrice());
        }

        if ($price->getReferencePrice() !== null) {
            $definition->setReferencePriceDefinition(
                new ReferencePriceDefinition(
                    $price->getReferencePrice()->getPurchaseUnit(),
                    $price->getReferencePrice()->getReferenceUnit(),
                    $price->getReferencePrice()->getUnitName()
                )
            );
        }

        return $definition;
    }
}
