<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;

trait CollectionLocationTrait
{
    public function getLeafletLocations(): array
    {
        return array_values($this->fmap(fn($entity) => /** @var EntityLocationTrait $entity */
$entity->getLeafletLocation()));
    }

    public function sortByLocationDistance(
        float $locationLat,
        float $locationLon,
        string $unit = "km",
        ?string $direction = null
    ): self
    {
        /** @var EntityLocationTrait $entity */
        foreach ($this as $entity) {
            $entity->setLocationDistance($locationLat, $locationLon, $unit);
        }

        if ($direction === FieldSorting::ASCENDING) {
            $this->sort(fn(EntityLocationTrait $a, EntityLocationTrait $b) => $a->getLocationDistance() > $b->getLocationDistance());
        } else if ($direction === FieldSorting::DESCENDING) {
            $this->sort(fn(EntityLocationTrait $a, EntityLocationTrait $b) => $a->getLocationDistance() < $b->getLocationDistance());
        }

        return $this;
    }
}
