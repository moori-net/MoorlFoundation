<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

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

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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

    /**
     * @param float $locationLat
     * @param float $locationLon
     * @param string $unit
     */
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
     * @return string
     */
    public function getLocationDistanceUnit(): string
    {
        return $this->locationDistanceUnit;
    }

    /**
     * @param string $locationDistanceUnit
     */
    public function setLocationDistanceUnit(string $locationDistanceUnit): void
    {
        $this->locationDistanceUnit = $locationDistanceUnit;
    }

    /**
     * @return float
     */
    public function getLocationDistance(): float
    {
        return $this->locationDistance;
    }

    /**
     * @return string|null
     */
    public function getMarkerId(): ?string
    {
        return $this->markerId;
    }

    /**
     * @param string|null $markerId
     */
    public function setMarkerId(?string $markerId): void
    {
        $this->markerId = $markerId;
    }

    /**
     * @return MarkerEntity|null
     */
    public function getMarker(): ?MarkerEntity
    {
        return $this->marker;
    }

    /**
     * @param MarkerEntity|null $marker
     */
    public function setMarker(?MarkerEntity $marker): void
    {
        $this->marker = $marker;
    }

    /**
     * @return bool
     */
    public function getAutoLocation(): bool
    {
        return $this->autoLocation;
    }

    /**
     * @param bool $autoLocation
     */
    public function setAutoLocation(bool $autoLocation): void
    {
        $this->autoLocation = $autoLocation;
    }

    /**
     * @return array
     */
    public function getLocationData(): array
    {
        return $this->locationData;
    }

    /**
     * @param array $locationData
     */
    public function setLocationData(array $locationData): void
    {
        $this->locationData = $locationData;
    }

    /**
     * @return float
     */
    public function getLocationLat(): float
    {
        return $this->locationLat;
    }

    /**
     * @param float $locationLat
     */
    public function setLocationLat(float $locationLat): void
    {
        $this->locationLat = $locationLat;
    }

    /**
     * @return float
     */
    public function getLocationLon(): float
    {
        return $this->locationLon;
    }

    /**
     * @param float $locationLon
     */
    public function setLocationLon(float $locationLon): void
    {
        $this->locationLon = $locationLon;
    }
}
