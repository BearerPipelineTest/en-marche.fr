<?php

namespace Tests\App\Security\Voter\CitizenProject;

use App\CitizenProject\CitizenProjectPermissions;
use App\Entity\Adherent;
use App\Entity\CitizenProject;
use App\Entity\CitizenProjectMembership;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\CitizenProject\CommentsCitizenProjectVoter;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\App\Security\Voter\AbstractAdherentVoterTest;

class CommentsCitizenProjectVoterTest extends AbstractAdherentVoterTest
{
    public function provideAnonymousCases(): iterable
    {
        // Anonymous should not be granted to comment projects, approved or not
        foreach (CitizenProjectPermissions::COMMENTS as $permission) {
            yield [false, true, $permission, $this->getCitizenProjectMock(true)];
        }

        foreach (CitizenProjectPermissions::COMMENTS as $permission) {
            yield [false, false, $permission, $this->getCitizenProjectMock(false)];
        }
    }

    protected function getVoter(): AbstractAdherentVoter
    {
        return new CommentsCitizenProjectVoter();
    }

    /**
     * @dataProvider provideCommentsCases
     */
    public function testCitizenProjectMemberCanCommentIfProjectApproved(
        string $attribute,
        bool $approved,
        bool $isMember
    ) {
        $project = $this->getCitizenProjectMock($approved);
        $adherent = $this->getAdherentMock($approved, $project, $isMember);

        $this->assertGrantedForAdherent($approved && $isMember, $approved, $adherent, $attribute, $project);
    }

    public function provideCommentsCases(): iterable
    {
        foreach (CitizenProjectPermissions::COMMENTS as $permission) {
            yield [$permission, true, false];
            yield [$permission, true, true];
            yield [$permission, false, true];
            yield [$permission, false, false];
        }
    }

    /**
     * @return Adherent|MockObject
     */
    public function getAdherentMock(bool $membershipChecked, CitizenProject $project, bool $isMember): Adherent
    {
        $adherent = $this->createAdherentMock();

        if ($membershipChecked) {
            $adherent->expects($this->once())
                ->method('getCitizenProjectMembershipFor')
                ->with($project)
                ->willReturn($isMember ? $this->createMock(CitizenProjectMembership::class) : null)
            ;
        } else {
            $adherent->expects($this->never())
                ->method('getCitizenProjectMembershipFor')
            ;
        }

        return $adherent;
    }

    /**
     * @return CitizenProject|MockObject
     */
    private function getCitizenProjectMock(bool $approved, bool $doubleChecked = false): CitizenProject
    {
        $project = $this->createMock(CitizenProject::class);

        $project->expects($doubleChecked ? $this->exactly(2) : $this->once())
            ->method('isApproved')
            ->willReturn($approved)
        ;

        return $project;
    }
}
