<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Cart;

use MoorlFoundation\Core\Content\CartCombinationDiscount\CartCombinationDiscountCollection;
use MoorlFoundation\Core\Content\CartCombinationDiscount\CartCombinationDiscountEntity;
use MoorlFoundation\Core\Content\CartCombinationDiscount\CartCombinationDiscountItemCollection;
use MoorlFoundation\Core\Content\CartCombinationDiscount\CartCombinationDiscountItemEntity;
use MoorlFoundation\Core\Service\CartCombinationDiscountService;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotCollection;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ProductBuyListDiscountCartProcessor implements CartProcessorInterface
{
    private ?CartCombinationDiscountCollection $combinationDiscounts = null;

    public function __construct(
        private readonly EntityRepository $cmsSlotRepository,
        private readonly CartCombinationDiscountService $cartCombinationDiscountService
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
        if (!$this->combinationDiscounts) {
            $cmsSlots = $this->getCmsSlots($context);
            if ($cmsSlots->count() === 0) {
                return;
            }

            $this->combinationDiscounts = $this->convertToCartCombinationDiscounts($cmsSlots);
            $this->cartCombinationDiscountService->addCombinationDiscounts($toCalculate, $this->combinationDiscounts);
            $this->cartCombinationDiscountService->process($toCalculate, $context);
        }
    }

    private function convertToCartCombinationDiscounts(CmsSlotCollection $cmsSlots): CartCombinationDiscountCollection
    {
        $combinationDiscounts = new CartCombinationDiscountCollection();

        /** @var CmsSlotEntity $cmsSlot */
        foreach ($cmsSlots as $cmsSlot) {
            if ($this->getDiscountValue($cmsSlot) == 0) {
                continue;
            }

            $productQuantities = $this->getProductQuantities($cmsSlot);
            if (count($productQuantities) === 0) {
                continue;
            }

            $combinationDiscountItems = new CartCombinationDiscountItemCollection();
            foreach ($productQuantities as $productId => $quantity) {
                $combinationDiscountItem = new CartCombinationDiscountItemEntity();
                $combinationDiscountItem->setId(md5($cmsSlot->getId() . $productId));
                $combinationDiscountItem->setProductId($productId);
                $combinationDiscountItem->setQuantity($quantity);
                $combinationDiscountItems->add($combinationDiscountItem);
            }
            $combinationDiscount = new CartCombinationDiscountEntity();
            $combinationDiscount->setId($cmsSlot->getId());
            $combinationDiscount->setDiscountValue($this->getDiscountValue($cmsSlot));
            $combinationDiscount->setMaxStacks($this->getMaxStacks($cmsSlot));
            $combinationDiscount->setTranslated(['name' => $this->getDiscountName($cmsSlot)]);
            $combinationDiscount->setItems($combinationDiscountItems);
            $combinationDiscounts->add($combinationDiscount);
        }

        return $combinationDiscounts;
    }

    private function getCmsSlots(SalesChannelContext $context): CmsSlotCollection
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('type', ['moorl-product-buy-list', 'moorl-shop-the-look']));
        $criteria->addFilter(new EqualsFilter('config.discountActive.value', "true"));

        return $this->cmsSlotRepository->search($criteria, $context->getContext())->getEntities();
    }

    private function getMaxStacks(CmsSlotEntity $cmsSlot): int
    {
        $config = $cmsSlot->getFieldConfig()->get('maxStacks');
        if ($config && $config->getValue()) {
            return (int) $config->getValue();
        }
        return 100;
    }

    private function getProductIds(CmsSlotEntity $cmsSlot): array
    {
        $config = $cmsSlot->getFieldConfig()->get('products');
        if ($config && $config->getValue()) {
            return (array) $config->getValue();
        }
        return [];
    }

    private function getDiscountValue(CmsSlotEntity $cmsSlot): float
    {
        $config = $cmsSlot->getFieldConfig()->get('discountPercentage');
        if ($config && $config->getValue()) {
            return (float) $config->getValue();
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
}
