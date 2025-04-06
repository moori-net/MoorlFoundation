<?php

namespace MoorlFoundation\Core\Framework\GeoLocation\Interfaces;
use MoorlFoundation\Core\Framework\GeoLocation\GeoPoint;
use MoorlFoundation\Core\Framework\GeoLocation\Polygon;

interface GeoPointInterface {
  public function inPolygon(Polygon $polygon);
  public function distanceTo(GeoPoint $geopoint, $unitofmeasure);
}

