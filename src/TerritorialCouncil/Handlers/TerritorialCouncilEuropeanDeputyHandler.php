<?php

namespace App\TerritorialCouncil\Handlers;

use App\Entity\ElectedRepresentative\MandateTypeEnum;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;

class TerritorialCouncilEuropeanDeputyHandler extends AbstractTerritorialCouncilElectedRepresentativeHandler
{
    protected function getQualityName(): string
    {
        return TerritorialCouncilQualityEnum::EUROPEAN_DEPUTY;
    }

    protected function getMandateTypes(): array
    {
        return [MandateTypeEnum::EURO_DEPUTY];
    }
}
