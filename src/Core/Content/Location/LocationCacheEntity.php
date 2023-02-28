<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Location;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class LocationCacheEntity extends Entity
{
    use EntityIdTrait;

    protected float $distance;

    public function getDistance(): float
    {
        return $this->distance;
    }

    public function setDistance(float $distance): void
    {
        $this->distance = $distance;
    }
}
