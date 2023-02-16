<?php

namespace AcMarche\Avaloir\Location;

use AcMarche\Avaloir\Entity\Avaloir;

class LocationMath
{
    public function calculate(Avaloir $avaloir): void
    {
        $latitude = $avaloir->getLatitude();
        $longitude = $avaloir->getLongitude();
        if ($longitude > 0 && $latitude > 0) {
            $cos_latitude = cos($latitude * pi() / 180.0);
            $cos_longitude = cos($longitude * pi() / 180.0);
            $sin_latitude = sin($latitude * pi() / 180.0);
            $sin_longitude = sin($longitude * pi() / 180.0);
            $avaloir->cos_longitude = $cos_longitude;
            $avaloir->cos_latitude = $cos_latitude;
            $avaloir->sin_longitude = $sin_longitude;
            $avaloir->sin_latitude = $sin_latitude;
        }
    }
}