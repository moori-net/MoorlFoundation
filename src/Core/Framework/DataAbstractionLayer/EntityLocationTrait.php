<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

use MoorlFoundation\Core\Content\Marker\MarkerEntity;

trait EntityLocationTrait
{
    protected float $locationLat = 0.00;
    protected float $locationLon = 0.00;
    protected array $locationData = [[0.00,0.00]];
    protected bool $autoLocation = false;
    protected ?string $markerId = null;
    protected ?MarkerEntity $marker = null;

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
