<?php

namespace Tests\App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\UserListDefinitionEnum;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\ManageUserListDefinitionsForTypeVoter;
use App\UserListDefinition\UserListDefinitionPermissions;
use PHPUnit\Framework\MockObject\MockObject;

class ManageUserListDefinitionsForTypeVoterTest extends AbstractAdherentVoterTest
{
    protected function getVoter(): AbstractAdherentVoter
    {
        return new ManageUserListDefinitionsForTypeVoter();
    }

    public function provideAnonymousCases(): iterable
    {
        yield [false, true, UserListDefinitionPermissions::ABLE_TO_MANAGE_TYPE, UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE];
    }

    public function providePermissions(): iterable
    {
        yield [UserListDefinitionPermissions::ABLE_TO_MANAGE_TYPE];
    }

    public function provideCorrectTypes(): iterable
    {
        foreach (UserListDefinitionEnum::TYPES as $type) {
            yield [$type];
        }
    }

    public function testAdherentIsNotGrantedIfTypeIsNotCorrect()
    {
        $adherent = $this->getAdherentMock();

        $this->assertGrantedForAdherent(
            false,
            true,
            $adherent,
            UserListDefinitionPermissions::ABLE_TO_MANAGE_TYPE,
            'failed_type'
        );
    }

    /**
     * @dataProvider provideCorrectTypes
     */
    public function testAdherentIsGrantedIfCorrectType(string $type)
    {
        $adherent = $this->getAdherentMock();

        $this->assertGrantedForAdherent(
            true,
            true,
            $adherent,
            UserListDefinitionPermissions::ABLE_TO_MANAGE_TYPE,
            $type
        );
    }

    public function testAdherentIsNotGrantedIfNotReferentAndElectedRepresentativeType()
    {
        $adherent = $this->getAdherentMock(false);

        $this->assertGrantedForAdherent(
            false,
            true,
            $adherent,
            UserListDefinitionPermissions::ABLE_TO_MANAGE_TYPE,
            UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE
        );
    }

    /**
     * @return Adherent|MockObject
     */
    private function getAdherentMock(bool $isReferent = true): Adherent
    {
        $adherent = $this->createAdherentMock();

        $adherent->expects($this->any())
            ->method('isReferent')
            ->willReturn($isReferent)
        ;

        return $adherent;
    }
}
