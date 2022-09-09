<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;

trait CollectionLocationTrait
{
    public function getLeafletLocations(): array
    {
        return array_values($this->fmap(function ($entity) {
            /** @var EntityLocationTrait $entity */
            return $entity->getLeafletLocation();
        }));
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

        /*$me = new LocationStruct();
        $me->setLocationLat($locationLat);
        $me->setLocationLon($locationLon);
        $this->addExtension('me', $me);*/

        if ($direction === FieldSorting::ASCENDING) {
            $this->sort(function (EntityLocationTrait $a, EntityLocationTrait $b) {
                return $a->getLocationDistance() > $b->getLocationDistance();
            });
        } else if ($direction === FieldSorting::DESCENDING) {
            $this->sort(function (EntityLocationTrait $a, EntityLocationTrait $b) {
                return $a->getLocationDistance() < $b->getLocationDistance();
            });
        }

        return $this;
    }
}
