<?php

namespace App\InstitutionalEvent;

use App\Entity\InstitutionalEvent;
use App\Geocoder\GeocodableEntityEventInterface;
use App\Geocoder\GeocodableInterface;
use App\Geocoder\GeoHashChangeAwareTrait;
use Symfony\Component\EventDispatcher\Event;

class InstitutionalEventEvent extends Event implements GeocodableEntityEventInterface
{
    use GeoHashChangeAwareTrait;

    protected $institutionalEvent;

    public function __construct(InstitutionalEvent $institutionalEvent)
    {
        $this->institutionalEvent = $institutionalEvent;
    }

    public function getInstitutionalEvent(): InstitutionalEvent
    {
        return $this->institutionalEvent;
    }

    public function getGeocodableEntity(): GeocodableInterface
    {
        return $this->institutionalEvent;
    }
}
