<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\GeoLocation;

use MoorlFoundation\Core\Framework\GeoLocation\Exceptions\InvalidArgumentException;
use MoorlFoundation\Core\Framework\GeoLocation\Exceptions\InvalidBoundingBoxCoordinatesException;

final class BoundingBox
{
    /** @var list<GeoPoint> */
    private array $geoPoints = [];

    public function __construct(
        private readonly float $minLat,
        private readonly float $minLon,
        private readonly float $maxLat,
        private readonly float $maxLon
    ) {
        $this->assertCoordinatesAreValid($this->minLat, $this->minLon, $this->maxLat, $this->maxLon);

        $this->geoPoints = [
            new GeoPoint($this->minLat, $this->minLon, false),
            new GeoPoint($this->minLat, $this->maxLon, false),
            new GeoPoint($this->maxLat, $this->minLon, false),
            new GeoPoint($this->maxLat, $this->maxLon, false),
        ];
    }

    public static function fromGeoPoint(GeoPoint $geoPoint, float $distance, string $unitOfMeasurement): self
    {
        $radius = Earth::getRadius($unitOfMeasurement);

        if ($radius <= 0.0 || $distance < 0.0) {
            throw new InvalidArgumentException('Bounding box distance must be greater than or equal to 0 and radius must be > 0.');
        }

        $angularDistance = $distance / $radius;

        $lat = $geoPoint->getLatitude(true);
        $lon = $geoPoint->getLongitude(true);

        $minLatRad = $lat - $angularDistance;
        $maxLatRad = $lat + $angularDistance;

        if ($minLatRad > Earth::getMINLAT() && $maxLatRad < Earth::getMAXLAT()) {
            $deltaLon = asin(sin($angularDistance) / cos($lat));

            $minLonRad = $lon - $deltaLon;
            if ($minLonRad < Earth::getMINLON()) {
                $minLonRad += 2 * pi();
            }

            $maxLonRad = $lon + $deltaLon;
            if ($maxLonRad > Earth::getMAXLON()) {
                $maxLonRad -= 2 * pi();
            }
        } else {
            $minLatRad = max($minLatRad, Earth::getMINLAT());
            $maxLatRad = min($maxLatRad, Earth::getMAXLAT());
            $minLonRad = Earth::getMINLON();
            $maxLonRad = Earth::getMAXLON();
        }

        return new self(
            rad2deg($minLatRad),
            rad2deg($minLonRad),
            rad2deg($maxLatRad),
            rad2deg($maxLonRad)
        );
    }

    public function getMinLongitude(): float
    {
        return $this->minLon;
    }

    public function getMinLatitude(): float
    {
        return $this->minLat;
    }

    public function getMaxLongitude(): float
    {
        return $this->maxLon;
    }

    public function getMaxLatitude(): float
    {
        return $this->maxLat;
    }

    /** @return list<GeoPoint> */
    public function getVertices(): array
    {
        return $this->geoPoints;
    }

    /** @return list<GeoPoint> */
    public function getGeoPoints(): array
    {
        return $this->geoPoints;
    }

    public function getPolygon(): Polygon
    {
        $polygon = new Polygon();
        foreach ($this->geoPoints as $vertex) {
            $polygon->addVertex($vertex);
        }
        return $polygon;
    }

    private function assertCoordinatesAreValid(float $minLat, float $minLon, float $maxLat, float $maxLon): void
    {
        foreach ([$minLat, $minLon, $maxLat, $maxLon] as $v) {
            if (is_nan($v) || is_infinite($v)) {
                throw new InvalidBoundingBoxCoordinatesException();
            }
        }

        if ($minLat < -90.0 || $minLat > 90.0 || $maxLat < -90.0 || $maxLat > 90.0) {
            throw new InvalidBoundingBoxCoordinatesException();
        }

        if ($minLon < -180.0 || $minLon > 180.0 || $maxLon < -180.0 || $maxLon > 180.0) {
            throw new InvalidBoundingBoxCoordinatesException();
        }

        if ($minLat > $maxLat) {
            throw new InvalidBoundingBoxCoordinatesException();
        }
    }
}
