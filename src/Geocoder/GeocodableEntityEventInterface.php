<?php

namespace App\Geocoder;

interface GeocodableEntityEventInterface
{
    /**
     * Returns the geocodable entity.
     */
    public function getGeocodableEntity(): GeocodableInterface;

    public function markAddressAsChanged(): void;

    public function isAddressChanged(): bool;
}
