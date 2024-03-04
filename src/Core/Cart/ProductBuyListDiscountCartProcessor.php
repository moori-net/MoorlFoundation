<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Cart;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartDataCollectorInterface;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\PercentagePriceCalculator;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotCollection;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Profiling\Profiler;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection;

class ProductBuyListDiscountCartProcessor implements CartProcessorInterface, CartDataCollectorInterface
{
    public function __construct(
        private readonly EntityRepository $cmsSlotRepository,
        private readonly PercentagePriceCalculator $percentagePriceCalculator
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
            $cmsSlot = $this->checkForMatch($toCalculate, $cmsSlots);
            if (!$cmsSlot) {
                return;
            }

            $calculated = new PriceCollection();
            $label = [];

            $productIds = $this->getProductIds($cmsSlot);
            foreach ($toCalculate->getLineItems() as $lineItem) {
                if (($key = array_search($lineItem->getReferencedId(), $productIds)) !== false) {
                    $lineItemPrice = $lineItem->getPrice();
                    $calculated->add($lineItemPrice);
                    $label[] = $lineItem->getLabel();
                    unset($productIds[$key]);
                }
            }

            $code = $cmsSlot->getId();
            $discountPercentage = $this->getDiscountPercentage($cmsSlot);
            $uniqueKey = 'discount-' . $code;
            $label = sprintf(
                "%s%% | %s",
                $discountPercentage,
                implode(', ', $label)
            );

            $item = new LineItem($uniqueKey, LineItem::CUSTOM_LINE_ITEM_TYPE);
            $item->setLabel($label);
            $item->setGood(false);
            $item->setReferencedId($code);
            $item->setPrice($this->percentagePriceCalculator->calculate($discountPercentage, $calculated, $context));


            $toCalculate->getLineItems()->add($item);

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
        $criteria->addFilter(new EqualsFilter('type', 'moorl-product-buy-list'));
        $criteria->addFilter(new RangeFilter('config.discountPercentage.value', [
            RangeFilter::GT => 0
        ]));

        return $this->cmsSlotRepository->search($criteria, $context->getContext())->getEntities();
    }

    private function checkForMatch(Cart $cart, CmsSlotCollection $cmsSlots): ?CmsSlotEntity
    {
        /** @var CmsSlotEntity $cmsSlot */
        foreach ($cmsSlots as $cmsSlot) {
            $productIds = $this->getProductIds($cmsSlot);
            $productQuantities = $this->getProductQuantities($cmsSlot);
            foreach ($cart->getLineItems() as $lineItem) {
                if (($key = array_search($lineItem->getReferencedId(), $productIds)) !== false) {
                    $quantity = 1;
                    if (isset($productQuantities[$lineItem->getReferencedId()])) {
                        $quantity = (int) $productQuantities[$lineItem->getReferencedId()];
                    }
                    if ($lineItem->getQuantity() !== $quantity) {
                        return null;
                    }
                    unset($productIds[$key]);
                }
                if (empty($productIds)) {
                    return $cmsSlot;
                }
            }
        }
        return null;
    }

    private function getProductIds(CmsSlotEntity $cmsSlot): array
    {
        $config = $cmsSlot->getFieldConfig()->get('products');
        if ($config && $config->getValue()) {
            return (array) $config->getValue();
        }
        return [];
    }

    private function getDiscountPercentage(CmsSlotEntity $cmsSlot): int
    {
        $config = $cmsSlot->getFieldConfig()->get('discountPercentage');
        if ($config && $config->getValue()) {
            return (int) - $config->getValue();
        }
        return 0;
    }

    private function getProductQuantities(CmsSlotEntity $cmsSlot): array
    {
        $config = $cmsSlot->getFieldConfig()->get('productQuantities');
        if ($config && $config->getValue()) {
            return (array) $config->getValue();
        }
        return [];
    }
}
