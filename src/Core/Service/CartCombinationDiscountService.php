<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Service;

use MoorlFoundation\Core\Content\CartCombinationDiscount\CartCombinationDiscountCollection;
use MoorlFoundation\Core\Content\CartCombinationDiscount\CartCombinationDiscountEntity;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\PercentagePriceCalculator;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\ReferencePriceDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Profiling\Profiler;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection;

class CartCombinationDiscountService
{
    public const PRODUCT_IDS = 'product_ids';
    public const PRODUCT_QUANTITIES = 'product_quantities';
    public const DISCOUNT_ID = 'discount_id';
    public const DISCOUNT_VALUE = 'discount_value';
    public const DISCOUNT_PRICE = 'discount_price';
    public const DISCOUNT_NAME = 'discount_name';
    public const DISCOUNT_MEDIA = 'discount_media';
    public const COMBINATION_STACKS = 'combination_stacks';

    private array $cartProductQuantities = [];
    private array $combinationDiscounts = [];
    private array $collectionCache = [];

    public function __construct(
        private readonly DefinitionInstanceRegistry $definitionInstanceRegistry,
        private readonly PercentagePriceCalculator $percentagePriceCalculator,
        private readonly QuantityPriceCalculator $quantityPriceCalculator
    )
    {
    }

    public function processByEntityName(
        Cart $cart,
        string $entityName,
        Criteria $criteria,
        SalesChannelContext $salesChannelContext
    ): void
    {
        if (isset($this->collectionCache[$entityName])) {
            $combinationDiscounts = $this->collectionCache[$entityName];
        } else {
            $repository = $this->definitionInstanceRegistry->getRepository($entityName);

            $combinationDiscounts = $repository->search($criteria, $salesChannelContext->getContext())->getEntities();

            $this->collectionCache[$entityName] = $combinationDiscounts;
        }

        $this->addCombinationDiscounts($cart, $combinationDiscounts);

        $this->process($cart, $salesChannelContext);
    }

    public function addCombinationDiscounts(Cart $cart, CartCombinationDiscountCollection $combinationDiscounts): void
    {
        $combinationDiscounts->sortByPriority();

        foreach ($combinationDiscounts as $combinationDiscount) {
            if ($cart->has($combinationDiscount->getId())) {
                continue;
            }

            $this->addCombinationDiscount($cart, $combinationDiscount);
        }
    }

    public function addCombinationDiscount(Cart $cart, CartCombinationDiscountEntity $combinationDiscount): void
    {
        $this->initCartProductQuantities($cart);

        if (count($this->cartProductQuantities) === 0) {
            return;
        }

        $combinationDiscountItems = $combinationDiscount->getItems();

        if (!$combinationDiscountItems?->count()) {
            return;
        }

        if ($combinationDiscountItems->count() > count($this->cartProductQuantities)) {
            return;
        }

        $combinationQuantities = array_filter($this->cartProductQuantities, function($k) use ($combinationDiscountItems) {
            return in_array($k, array_keys($combinationDiscountItems->getProductQuantities()));
        }, ARRAY_FILTER_USE_KEY);
        if (count($combinationQuantities) !== count($combinationDiscountItems->getProductQuantities())) {
            return;
        }

        $subtracted = array_map(function($x, $y) {
            return (int) floor($x / $y);
        }, $combinationQuantities, $combinationDiscountItems->getProductQuantities());
        $combinationStacks = min($subtracted);
        if (!$combinationStacks) {
            return;
        }

        if ($combinationStacks > $combinationDiscount->getMaxStacks()) {
            $combinationStacks = $combinationDiscount->getMaxStacks();
        }

        foreach ($combinationDiscountItems->getProductQuantities() as $key => $qty) {
            $this->cartProductQuantities[$key] = $this->cartProductQuantities[$key] - ($combinationStacks * $qty);
        }

        $this->combinationDiscounts[] = [
            self::PRODUCT_IDS => array_keys($combinationDiscountItems->getProductQuantities()),
            self::PRODUCT_QUANTITIES => $combinationDiscountItems->getProductQuantities(),
            self::DISCOUNT_ID => $combinationDiscount->getId(),
            self::DISCOUNT_VALUE => - $combinationDiscount->getDiscountValue(),
            self::DISCOUNT_PRICE => $combinationDiscount->getDiscountPrice(),
            self::DISCOUNT_NAME => $combinationDiscount->getTranslation('name'),
            self::DISCOUNT_MEDIA => $combinationDiscount->getMedia(),
            self::COMBINATION_STACKS => $combinationStacks,
        ];
    }

    public function process(Cart $cart, SalesChannelContext $salesChannelContext): void
    {
        Profiler::trace('cart::combination-discount::process', function () use ($cart, $salesChannelContext): void {
            if (empty($this->combinationDiscounts)) {
                return;
            }
            if (empty($this->cartProductQuantities)) {
                return;
            }

            foreach ($this->combinationDiscounts as $combinationDiscount) {
                if ($cart->has($combinationDiscount[self::DISCOUNT_ID])) {
                    continue;
                }

                $calculated = new PriceCollection();
                $label = [];

                foreach ($cart->getLineItems() as $lineItem) {
                    if (in_array($lineItem->getReferencedId(), $combinationDiscount[self::PRODUCT_IDS])) {
                        $calculatedQuantity = $combinationDiscount[self::COMBINATION_STACKS] * $combinationDiscount[self::PRODUCT_QUANTITIES][$lineItem->getReferencedId()];

                        $calculated->add(
                            $this->quantityPriceCalculator->calculate(
                                $this->buildPriceDefinition($lineItem->getPrice(), $calculatedQuantity),
                                $salesChannelContext
                            )
                        );
                        $label[] = sprintf("%dx %s", $calculatedQuantity, $lineItem->getLabel());
                    }
                }

                $label = sprintf(
                    "%s%% | %s",
                    $combinationDiscount[self::DISCOUNT_VALUE],
                    $combinationDiscount[self::DISCOUNT_NAME] ? sprintf("%dx %s", $combinationDiscount[self::COMBINATION_STACKS], $combinationDiscount[self::DISCOUNT_NAME]) : implode(', ', $label)
                );

                $item = new LineItem($combinationDiscount[self::DISCOUNT_ID], LineItem::CUSTOM_LINE_ITEM_TYPE);
                $item->setLabel($label);
                $item->setCover($combinationDiscount[self::DISCOUNT_MEDIA]);
                $item->setGood(false);
                $item->setReferencedId($combinationDiscount[self::DISCOUNT_ID]);
                $item->setPrice($this->percentagePriceCalculator->calculate($combinationDiscount[self::DISCOUNT_VALUE], $calculated, $salesChannelContext));

                $cart->getLineItems()->add($item);
            }
        }, 'cart');
    }

    private function initCartProductQuantities(Cart $cart): void
    {
        if (!empty($this->cartProductQuantities)) {
            return;
        }

        foreach ($cart->getLineItems()->filterType(LineItem::PRODUCT_LINE_ITEM_TYPE) as $lineItem) {
            $this->cartProductQuantities[$lineItem->getReferencedId()] = $lineItem->getQuantity();
        }
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
