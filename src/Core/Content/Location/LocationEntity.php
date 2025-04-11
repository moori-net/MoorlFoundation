<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Location;

use MoorlFoundation\Core\Framework\GeoLocation\GeoPoint;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class LocationEntity extends Entity
{
    use EntityIdTrait;

    protected array $payload = [];
    protected float $locationLat = 0;
    protected float $locationLon = 0;

    public function getGeoPoint(): GeoPoint
    {
        return new GeoPoint($this->locationLat, $this->locationLon);
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function setPayload(array $payload): void
    {
        $this->payload = $payload;
    }

    public function getLocationLat(): float
    {
        return $this->locationLat;
    }

    public function setLocationLat(float $locationLat): void
    {
        $this->locationLat = $locationLat;
    }

    public function getLocationLon(): float
    {
        return $this->locationLon;
    }

    public function setLocationLon(float $locationLon): void
    {
        $this->locationLon = $locationLon;
    }
}
