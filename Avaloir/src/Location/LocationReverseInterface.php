<?php


namespace AcMarche\Avaloir\Location;


interface LocationReverseInterface
{
    /**
     * @param $latitude
     * @param $longitude
     */
    public function reverse($latitude, $longitude): array;

    public function getRoad(): ?string;

    public function getLocality(): ?string;

    public function getHouseNumber(): ?string;

}
