<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

trait EntityLocationTrait
{
    protected float $locationLat = 0.00;
    protected float $locationLon = 0.00;
    protected array $locationData = [[0.00,0.00]];
    protected bool $autoLocation = false;

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
