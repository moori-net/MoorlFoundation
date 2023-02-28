<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

use MoorlFoundation\Core\Content\Location\LocationCacheCollection;
use MoorlFoundation\Core\Content\Marker\MarkerEntity;

trait EntityLocationTrait
{
    protected float $locationLat = 0.00;
    protected float $locationLon = 0.00;
    protected float $locationDistance = 0.00;
    protected string $locationDistanceUnit = "km";
    protected array $locationData = [[0.00,0.00]];
    protected bool $autoLocation = false;
    protected ?string $markerId = null;
    protected ?MarkerEntity $marker = null;
    protected ?LocationCacheCollection $locationCache = null;

    public function getLeafletLocation(): array
    {
        return [
            'latlng' => [
                $this->locationLat,
                $this->locationLon,
            ],
            'icon' => $this->marker ? $this->marker->getLeafletMarker() : false,
        ];
    }

    public function getLeafletCircle(): array
    {
        return [
            'latlng' => [
                $this->locationLat,
                $this->locationLon,
            ],
            'radius' => $this->locationDistance * 1000,
            'unit' => $this->locationDistanceUnit,
        ];
    }

    public function setLocationDistance(float $locationLat, float $locationLon, string $unit = "km"): void
    {
        if (!$this->locationLat || !$this->locationLon) {
            return;
        }

        $this->locationDistanceUnit = $unit;

        $theta = $this->locationLon - $locationLon;
        $dist = sin(deg2rad($this->locationLat)) * sin(deg2rad($locationLat)) + cos(deg2rad($this->locationLat)) * cos(deg2rad($locationLat)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;

        if ($unit === "km") {
            $this->locationDistance = ($miles * 1.609344);
        } else if ($unit === "nm") {
            $this->locationDistance = ($miles * 0.8684);
        } else {
            $this->locationDistance = $miles;
        }
    }

    /**
     * @return LocationCacheCollection|null
     */
    public function getLocationCache(): ?LocationCacheCollection
    {
        return $this->locationCache;
    }

    /**
     * @param LocationCacheCollection|null $locationCache
     */
    public function setLocationCache(?LocationCacheCollection $locationCache): void
    {
        $this->locationCache = $locationCache;
    }

    public function getLocationDistanceUnit(): string
    {
        return $this->locationDistanceUnit;
    }

    public function setLocationDistanceUnit(string $locationDistanceUnit): void
    {
        $this->locationDistanceUnit = $locationDistanceUnit;
    }

    public function getLocationDistance(): float
    {
        return $this->locationDistance;
    }

    public function getMarkerId(): ?string
    {
        return $this->markerId;
    }

    public function setMarkerId(?string $markerId): void
    {
        $this->markerId = $markerId;
    }

    public function getMarker(): ?MarkerEntity
    {
        return $this->marker;
    }

    public function setMarker(?MarkerEntity $marker): void
    {
        $this->marker = $marker;
    }

    public function getAutoLocation(): bool
    {
        return $this->autoLocation;
    }

    public function setAutoLocation(bool $autoLocation): void
    {
        $this->autoLocation = $autoLocation;
    }

    public function getLocationData(): array
    {
        return $this->locationData;
    }

    public function setLocationData(array $locationData): void
    {
        $this->locationData = $locationData;
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
