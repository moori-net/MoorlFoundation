<?php

namespace MoorlFoundation\Core\Framework\GeoLocation\Interfaces;

interface Polygon
{
    public function __construct($array);

    public function surroundsGeoPoint();
}
