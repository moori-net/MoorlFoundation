<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\PartsList;

use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                       add(PartsListEntity $entity)
 * @method void                       set(string $key, PartsListEntity $entity)
 * @method PartsListEntity[]    getIterator()
 * @method PartsListEntity[]    getElements()
 * @method PartsListEntity|null get(string $key)
 * @method PartsListEntity|null first()
 * @method PartsListEntity|null last()
 */
class PartsListCollection extends EntityCollection
{
    public static function createFromProducts(ProductCollection $products): self
    {
        $self = new self();

        foreach ($products as $product) {
            $self->add(PartsListEntity::createFromProduct($product));
        }

        return $self;
    }

    protected function getExpectedClass(): string
    {
        return PartsListEntity::class;
    }

    public function filterByProductStreamIds(?array $productStreamIds = null): self
    {
        $containsAll = fn(array $needles, array $haystack): bool => empty(array_diff($needles, $haystack));

        return $this->filter(
            static fn(PartsListEntity $entity) => $entity->getProduct()->getStreamIds() === null || ($productStreamIds && $containsAll($productStreamIds, $entity->getProduct()->getStreamIds()))
        );
    }

    public function filterByProductIds(?array $productIds = null): self
    {
        return $this->filter(
            static fn(PartsListEntity $entity) => $entity->getProductId() === null || ($productIds && in_array($entity->getProductId(), $productIds))
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
        $this->sort(fn(PartsListEntity $a, PartsListEntity $b) => $b->getPriority() <=> $a->getPriority());
    }
}
