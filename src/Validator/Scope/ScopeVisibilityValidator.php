<?php

namespace App\Validator\Scope;

use App\Entity\Adherent;
use App\Entity\EntityScopeVisibilityInterface;
use App\Geo\ManagedZoneProvider;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ScopeVisibilityValidator extends ConstraintValidator
{
    private Security $security;
    private ManagedZoneProvider $managedZoneProvider;
    private ScopeGeneratorResolver $scopeGeneratorResolver;

    public function __construct(
        Security $security,
        ManagedZoneProvider $managedZoneProvider,
        ScopeGeneratorResolver $scopeGeneratorResolver
    ) {
        $this->security = $security;
        $this->managedZoneProvider = $managedZoneProvider;
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
    }

    /**
     * @param EntityScopeVisibilityInterface $value
     * @param ScopeVisibility|Constraint     $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ScopeVisibility) {
            throw new UnexpectedTypeException($constraint, ScopeVisibility::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof EntityScopeVisibilityInterface) {
            throw new UnexpectedValueException($value, EntityScopeVisibilityInterface::class);
        }

        $scope = $this->scopeGeneratorResolver->generate();

        if (!$scope) {
            return;
        }

        if ($scope->isNational()) {
            if (!$value->isNationalVisibility()) {
                $this
                    ->context
                    ->buildViolation($constraint->nationalScopeWithZoneMessage)
                    ->atPath('zone')
                    ->addViolation()
                ;
            }

            return;
        }

        if ($value->isNationalVisibility()) {
            $this
                ->context
                ->buildViolation($constraint->localScopeWithoutZoneMessage)
                ->atPath('zone')
                ->addViolation()
            ;

            return;
        }

        if (!$this->managedZoneProvider->zoneBelongsToSomeZones($value->getZone(), $scope->getZones())) {
            $this
                ->context
                ->buildViolation($constraint->localScopeWithUnmanagedZoneMessage)
                ->atPath('zone')
                ->addViolation()
            ;
        }
    }

    private function getAdherent(): Adherent
    {
        return $this->security->getUser();
    }
}
