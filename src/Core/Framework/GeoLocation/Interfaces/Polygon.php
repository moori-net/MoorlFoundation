<?php

namespace MoorlFoundation\Core\Framework\GeoLocation\Interfaces;

use MoorlFoundation\Core\Framework\GeoLocation\GeoPoint;

interface Polygon
{
    public function __construct($array);

    public function surroundsGeoPoint();
}
