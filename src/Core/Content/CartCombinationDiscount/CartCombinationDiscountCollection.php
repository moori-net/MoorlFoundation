<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\CartCombinationDiscount;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                       add(CartCombinationDiscountEntity $entity)
 * @method void                       set(string $key, CartCombinationDiscountEntity $entity)
 * @method CartCombinationDiscountEntity[]    getIterator()
 * @method CartCombinationDiscountEntity[]    getElements()
 * @method CartCombinationDiscountEntity|null get(string $key)
 * @method CartCombinationDiscountEntity|null first()
 * @method CartCombinationDiscountEntity|null last()
 */
class CartCombinationDiscountCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return CartCombinationDiscountEntity::class;
    }

    public function filterByProduct(ProductEntity $product): self
    {
        return $this->filter(
            static fn(CartCombinationDiscountEntity $entity) => $entity->getItems()?->hasProductStreamIds($product->getStreamIds()) || $entity->getItems()?->hasProductId($product->getId())
        );
    }

    public function sortByPriority(): void
    {
        $this->sort(fn(CartCombinationDiscountEntity $a, CartCombinationDiscountEntity $b) => $b->getPriority() <=> $a->getPriority());
    }
}
