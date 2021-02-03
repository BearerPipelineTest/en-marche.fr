<?php

namespace Tests\App\Security\Voter\CitizenProject;

use App\CitizenProject\CitizenProjectPermissions;
use App\Entity\Adherent;
use App\Entity\CitizenProject;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\CitizenProject\AdministrateCitizenProjectVoter;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\UuidInterface;
use Tests\App\Security\Voter\AbstractAdherentVoterTest;

class AdministrateCitizenProjectVoterTest extends AbstractAdherentVoterTest
{
    public function provideAnonymousCases(): iterable
    {
        yield [false, true, CitizenProjectPermissions::ADMINISTRATE, $this->createMock(CitizenProject::class)];
    }

    protected function getVoter(): AbstractAdherentVoter
    {
        return new AdministrateCitizenProjectVoter();
    }

    public function testAdherentCannotAdministrateCitizenProjectIfNotApproved()
    {
        $citizenProject = $this->getCitizenProjectMock(false, false);
        $adherent = $this->getAdherentMock(true);

        $this->assertGrantedForAdherent(false, true, $adherent, CitizenProjectPermissions::ADMINISTRATE, $citizenProject);
    }

    public function testAdherentCanAdministrateCitizenProjectIfNotApprovedButCreator()
    {
        $citizenProject = $this->getCitizenProjectMock(false, true);
        $adherent = $this->getAdherentMock(true);

        $this->assertGrantedForAdherent(true, true, $adherent, CitizenProjectPermissions::ADMINISTRATE, $citizenProject);
    }

    public function testAdherentCanAdministrateCitizenProjectIfApprovedAndAdministrator()
    {
        $citizenProject = $this->getCitizenProjectMock(true);
        $adherent = $this->getAdherentMock(false, $citizenProject, false);

        // A "standard" adherent should not be granted
        $this->assertGrantedForAdherent(false, true, $adherent, CitizenProjectPermissions::ADMINISTRATE, $citizenProject);

        $citizenProject = $this->getCitizenProjectMock(true);
        $adherent = $this->getAdherentMock(false, $citizenProject, true);

        // An of this project should be granted
        $this->assertGrantedForAdherent(true, true, $adherent, CitizenProjectPermissions::ADMINISTRATE, $citizenProject);
    }

    /**
     * @return Adherent|MockObject
     */
    private function getAdherentMock(
        bool $getUuidIsCalled,
        CitizenProject $project = null,
        bool $isAdministrator = null
    ): Adherent {
        $adherent = $this->createAdherentMock();

        if ($getUuidIsCalled) {
            $adherent->expects($this->once())
                ->method('getUuid')
                ->willReturn($this->createMock(UuidInterface::class))
            ;
        } else {
            $adherent->expects($this->never())
                ->method('getUuid')
            ;
        }

        if ($project) {
            $adherent->expects($this->once())
                ->method('isAdministratorOf')
                ->with($project)
                ->willReturn($isAdministrator)
            ;
        } else {
            $adherent->expects($this->never())
                ->method('isAdministratorOf')
            ;
        }

        return $adherent;
    }

    /**
     * @return CitizenProject|MockObject
     */
    private function getCitizenProjectMock(bool $approved, bool $withCreator = null): CitizenProject
    {
        $project = $this->createMock(CitizenProject::class);

        $project->expects($this->once())
            ->method('isApproved')
            ->willReturn($approved)
        ;

        if ($approved) {
            $project->expects($this->never())
                ->method('isCreatedBy')
            ;
        } elseif (null !== $withCreator) {
            $project->expects($this->once())
                ->method('isCreatedBy')
                ->with($this->isInstanceOf(UuidInterface::class))
                ->willReturn($withCreator)
            ;
        }

        return $project;
    }
}
