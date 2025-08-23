<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;

trait CollectionLocationTrait
{
    public function getLeafletLocations(): array
    {
        return array_values($this->fmap(fn($entity) => $entity->getLeafletLocation()));
    }

    public function sortByLocationDistance(
        float $locationLat,
        float $locationLon,
        string $unit = "km",
        string $direction = FieldSorting::ASCENDING
    ): self
    {
        foreach ($this as $entity) {
            $entity->setLocationDistance($locationLat, $locationLon, $unit);
        }

        if ($direction === FieldSorting::ASCENDING) {
            $this->sort(fn($a, $b) => $a->getLocationDistance() > $b->getLocationDistance());
        } else if ($direction === FieldSorting::DESCENDING) {
            $this->sort(fn($a, $b) => $a->getLocationDistance() < $b->getLocationDistance());
        }

        return $this;
    }
}
