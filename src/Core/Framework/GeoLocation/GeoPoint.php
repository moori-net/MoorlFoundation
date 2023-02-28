<?php

namespace MoorlFoundation\Core\Framework\GeoLocation;

use MoorlFoundation\Core\Framework\GeoLocation\Exceptions\NoApiKeyException;
use MoorlFoundation\Core\Framework\GeoLocation\Polygon;
use MoorlFoundation\Core\Framework\GeoLocation\Base\GeoLocation;
use MoorlFoundation\Core\Framework\GeoLocation\Earth;
use MoorlFoundation\Core\Framework\GeoLocation\BoundingBox;
use MoorlFoundation\Core\Framework\GeoLocation\Exceptions\OutOfBoundsException;
use MoorlFoundation\Core\Framework\GeoLocation\Exceptions\UnexpectedResponseException;

class GeoPoint
{
    protected $radLat;  // latitude in radians
    protected $radLon;  // longitude in radians
    protected $degLat;     // latitude in degrees
    protected $degLon;  // longitude in degrees

    /**
     * @param double $latitude the latitude
     * @param double $longitude the longitude
     * @param bool $inRadians true if latitude and longitude are in radians
     * @return GeoPoint
     */
    public function __construct($latitude, $longitude, $inRadians = false)
    {
        if ($inRadians) {
            $this->radLat = $latitude;
            $this->radLon = $longitude;
            $this->degLat = rad2deg($latitude);
            $this->degLon = rad2deg($longitude);
        } else {
            $this->radLat = $latitude * M_PI / 180;
            $this->radLon = $longitude * M_PI / 180;
            $this->degLat = $latitude;
            $this->degLon = $longitude;
        }
        $this->checkBounds();
    }

    public function boundingBox($distance, $unit_of_measurement)
    {
        return BoundingBox::fromGeoPoint($this, $distance, $unit_of_measurement);
    }

    /**
     * Computes the great circle distance between this GeoLocation instance
     * and the location argument.
     * @param \MoorlFoundation\Core\Framework\GeoLocation\GeoLocation $location
     * @param string $unit_of_measurement
     * @return double the distance, measured in the same unit as the radius
     * argument.
     * @internal param float $radius the radius of the sphere, e.g. the average radius for a
     * spherical approximation of the figure of the Earth is approximately
     * 6371.01 kilometers.
     */
    public function distanceTo(GeoPoint $geopoint, $unit_of_measurement)
    {
        $radius = Earth::getRadius($unit_of_measurement);

        return acos(sin($this->radLat) * sin($geopoint->radLat) +
                cos($this->radLat) * cos($geopoint->radLat) *
                cos($this->radLon - $geopoint->radLon)) * $radius;
    }

    public function inPolygon(Polygon $polygon)
    {
        return $polygon->containsGeoPoint($this);
    }

    public function getLatitude($inRadians = false)
    {
        return (!$inRadians) ? $this->degLat : $this->radLat;
    }

    public function getLongitude($inRadians = false)
    {
        return (!$inRadians) ? $this->degLon : $this->radLon;
    }

    public function getRadLat(): float|int
    {
        return $this->radLat;
    }

    public function setRadLat(float|int $radLat)
    {
        $this->radLat = $radLat;
    }

    public function getRadLon(): float|int
    {
        return $this->radLon;
    }

    public function setRadLon(float|int $radLon)
    {
        $this->radLon = $radLon;
    }

    /**
     * @return mixed
     */
    public function getDegLat()
    {
        return $this->degLat;
    }

    public function setDegLat(mixed $degLat)
    {
        $this->degLat = $degLat;
    }

    /**
     * @return mixed
     */
    public function getDegLon()
    {
        return $this->degLon;
    }

    public function setDegLon(mixed $degLon)
    {
        $this->degLon = $degLon;
    }

    /**
     * @description checks lat and long are within bounds of our elipsoid
     * @throws OutOfBoundsException
     */
    protected function checkBounds()
    {
        if ($this->radLat < Earth::getMINLAT() || $this->radLat > Earth::getMAXLAT() ||
            $this->radLon < Earth::getMINLON() || $this->radLon > Earth::getMAXLON())
            throw new OutOfBoundsException("Check your latitude and longitude inputs");
    }

    /**
     * @param $address
     * @param $apiKey
     * @return mixed
     * @throws \Exception
     * @throws UnexpectedResponseException if Google sends us something that we don't expect. we only like nice presents not 500 errors and the like
     * @throws NoApiKeyException if you forget to pass a google API key. create one at https://console.cloud.google.com for Geocoding API
     */
    public static function fromAddress($address, $apiKey = null)
    {
        if (!$apiKey) {
            throw new NoApiKeyException();
        }
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode((string) $address) . '&sensor=false&key=' . $apiKey;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (curl_error($ch)) {
            throw new \Exception(curl_error($ch));
        }
        $response = json_decode(curl_exec($ch));
        curl_close($ch);
        if (!is_object($response) || !isset($response->results[0]->geometry->location->lat) || !isset($response->results[0]->geometry->location->lng)) {
            if (isset($response->error_message))
                throw new UnexpectedResponseException($response->error_message);
            else
                throw new UnexpectedResponseException();
        } else {
            return new self($response->results[0]->geometry->location->lat, $response->results[0]->geometry->location->lng);
        }
    }
}

