<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Location;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class LocationCacheEntity extends Entity
{
    use EntityIdTrait;

    protected float $distance;

    /**
     * @return float
     */
    public function getDistance(): float
    {
        return $this->distance;
    }

    /**
     * @param float $distance
     */
    public function setDistance(float $distance): void
    {
        $this->distance = $distance;
    }
}
