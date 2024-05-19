<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\CartCombinationDiscount;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                       add(CartCombinationDiscountItemEntity $entity)
 * @method void                       set(string $key, CartCombinationDiscountItemEntity $entity)
 * @method CartCombinationDiscountItemEntity[]    getIterator()
 * @method CartCombinationDiscountItemEntity[]    getElements()
 * @method CartCombinationDiscountItemEntity|null get(string $key)
 * @method CartCombinationDiscountItemEntity|null first()
 * @method CartCombinationDiscountItemEntity|null last()
 */
class CartCombinationDiscountItemCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return CartCombinationDiscountItemEntity::class;
    }

    public function filterByProductStreamIds(?array $productStreamIds = null): self
    {
        return $this->filter(
            static fn(CartCombinationDiscountItemEntity $entity) => $entity->getProductStreamId() === null || ($productStreamIds && in_array($entity->getProductStreamId(), $productStreamIds))
        );
    }

    public function filterByProductIds(?array $productIds = null): self
    {
        return $this->filter(
            static fn(CartCombinationDiscountItemEntity $entity) => $entity->getProductId() === null || ($productIds && in_array($entity->getProductId(), $productIds))
        );
    }

    public function hasProductStreamIds(?array $productStreamIds = null): bool
    {
        if (!$productStreamIds) {
            return false;
        }
        foreach ($this->getElements() as $entity) {
            if ($entity->getProductStreamId() && (in_array($entity->getProductStreamId(), $productStreamIds))) {
                return true;
            }

        }
        return false;
    }

    public function hasProductId(string $productId): bool
    {
        foreach ($this->getElements() as $entity) {
            if ($entity->getProductId() === $productId) {
                return true;
            }

        }
        return false;
    }

    public function getProductQuantities(): array
    {
        $productQuantities = [];

        foreach ($this->getElements() as $entity) {
            $productQuantities[$entity->getProductId()] = $entity->getQuantity();
        }

        return $productQuantities;
    }

    public function sortByPriority(): void
    {
        $this->sort(fn(CartCombinationDiscountItemEntity $a, CartCombinationDiscountItemEntity $b) => $b->getPriority() <=> $a->getPriority());
    }
}
