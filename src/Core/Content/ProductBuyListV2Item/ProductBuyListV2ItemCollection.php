<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\ProductBuyListV2Item;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                       add(ProductBuyListV2ItemEntity $entity)
 * @method void                       set(string $key, ProductBuyListV2ItemEntity $entity)
 * @method ProductBuyListV2ItemEntity[]    getIterator()
 * @method ProductBuyListV2ItemEntity[]    getElements()
 * @method ProductBuyListV2ItemEntity|null get(string $key)
 * @method ProductBuyListV2ItemEntity|null first()
 * @method ProductBuyListV2ItemEntity|null last()
 */
class ProductBuyListV2ItemCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ProductBuyListV2ItemEntity::class;
    }

    public function filterByProductStreamIds(?array $productStreamIds = null): self
    {
        return $this->filter(
            static fn(ProductBuyListV2ItemEntity $entity) => $entity->getProductStreamId() === null || ($productStreamIds && in_array($entity->getProductStreamId(), $productStreamIds))
        );
    }

    public function filterByProductIds(?array $productIds = null): self
    {
        return $this->filter(
            static fn(ProductBuyListV2ItemEntity $entity) => $entity->getProductId() === null || ($productIds && in_array($entity->getProductId(), $productIds))
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
        $this->sort(fn(ProductBuyListV2ItemEntity $a, ProductBuyListV2ItemEntity $b) => $b->getPriority() <=> $a->getPriority());
    }
}
