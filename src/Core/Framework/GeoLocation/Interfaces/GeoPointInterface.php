<?php

namespace MoorlFoundation\Core\Framework\GeoLocation\Interfaces;
use MoorlFoundation\Core\Framework\GeoLocation\Polygon;
use MoorlFoundation\Core\Framework\GeoLocation\GeoPoint;

interface GeoPointInterface {
  public function inPolygon(Polygon $polygon);
  public function distanceTo(GeoPoint $geopoint, $unitofmeasure);
}

