<?php

namespace App\Membership;

use App\Entity\Adherent;
use App\Geocoder\GeocodableEntityEventInterface;
use App\Geocoder\GeocodableInterface;
use App\Geocoder\GeoHashChangeAwareTrait;
use Symfony\Component\EventDispatcher\Event;

class AdherentEvent extends Event implements GeocodableEntityEventInterface
{
    use GeoHashChangeAwareTrait;

    private $adherent;

    public function __construct(Adherent $adherent)
    {
        $this->adherent = $adherent;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function getGeocodableEntity(): GeocodableInterface
    {
        return $this->adherent;
    }
}
