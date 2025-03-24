<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\ProductBuyList;

use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                       add(ProductBuyListItemEntity $entity)
 * @method void                       set(string $key, ProductBuyListItemEntity $entity)
 * @method ProductBuyListItemEntity[]    getIterator()
 * @method ProductBuyListItemEntity[]    getElements()
 * @method ProductBuyListItemEntity|null get(string $key)
 * @method ProductBuyListItemEntity|null first()
 * @method ProductBuyListItemEntity|null last()
 */
class ProductBuyListItemCollection extends EntityCollection
{
    public static function createFromProducts(ProductCollection $products): self
    {
        $self = new self();

        foreach ($products as $product) {
            $self->add(ProductBuyListItemEntity::createFromProduct($product));
        }

        return $self;
    }

    protected function getExpectedClass(): string
    {
        return ProductBuyListItemEntity::class;
    }

    public function filterByProductStreamIds(?array $productStreamIds = null): self
    {
        $containsAll = fn(array $needles, array $haystack): bool => empty(array_diff($needles, $haystack));

        return $this->filter(
            static fn(ProductBuyListItemEntity $entity) => $entity->getProduct()->getStreamIds() === null || ($productStreamIds && $containsAll($productStreamIds, $entity->getProduct()->getStreamIds()))
        );
    }

    public function filterByProductIds(?array $productIds = null): self
    {
        return $this->filter(
            static fn(ProductBuyListItemEntity $entity) => $entity->getProductId() === null || ($productIds && in_array($entity->getProductId(), $productIds))
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
        $this->sort(fn(ProductBuyListItemEntity $a, ProductBuyListItemEntity $b) => $b->getPriority() <=> $a->getPriority());
    }
}
