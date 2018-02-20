<?php

namespace Tests\AppBundle\Security\Voter;

use AppBundle\Entity\Adherent;
use AppBundle\Security\Voter\AbstractAdherentVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractAdherentVoterTest extends TestCase
{
    /**
     * @var AbstractAdherentVoter
     */
    protected $voter;

    protected function setUp(): void
    {
        $this->voter = $this->getVoter();
    }

    protected function tearDown(): void
    {
        $this->voter = null;
    }

    abstract public function provideAnonymousCases(): iterable;

    abstract protected function getVoter(): AbstractAdherentVoter;

    /**
     * @dataProvider provideAnonymousCases
     */
    public function testAnonymousIsGranted(bool $granted, bool $adherentInstanceChecked, string $attribute, $subject = null): void
    {
        if ($granted) {
            $this->assertSame(
                VoterInterface::ACCESS_GRANTED,
                $this->voter->vote($this->createTokenMock(null, $adherentInstanceChecked), $subject, [$attribute]),
                'Anonymous user should be granted.'
            );
        } else {
            $this->assertSame(
                VoterInterface::ACCESS_DENIED,
                $this->voter->vote($this->createTokenMock(null, $adherentInstanceChecked), $subject, [$attribute]),
                'Anonymous user should not be granted'
            );
        }
    }

    protected function assertGrantedForAdherent(
        bool $granted,
        bool $instanceIsChecked,
        Adherent $adherent,
        string $attribute,
        $subject = null
    ): void {
        if ($granted) {
            $this->assertSame(
                VoterInterface::ACCESS_GRANTED,
                $this->voter->vote($this->createTokenMock($adherent, $instanceIsChecked), $subject, [$attribute]),
                'Adherent should be granted.'
            );
        } else {
            $this->assertSame(
                VoterInterface::ACCESS_DENIED,
                $this->voter->vote($this->createTokenMock($adherent, $instanceIsChecked), $subject, [$attribute]),
                'Adherent should not be granted.'
            );
        }
    }

    protected function assertGrantedForUser(
        bool $granted,
        bool $instanceIsChecked,
        Adherent $adherent,
        string $attribute,
        $subject = null
    ): void {
        if ($granted) {
            $this->assertSame(
                VoterInterface::ACCESS_GRANTED,
                $this->voter->vote($this->createTokenMock($adherent, $instanceIsChecked), $subject, [$attribute]),
                'User should be granted.'
            );
        } else {
            $this->assertSame(
                VoterInterface::ACCESS_DENIED,
                $this->voter->vote($this->createTokenMock($adherent, $instanceIsChecked), $subject, [$attribute]),
                'User should not be granted.'
            );
        }
    }

    /**
     * @param UserInterface|null $adherent
     * @param bool               $adherentInstanceChecked
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|TokenInterface
     */
    protected function createTokenMock(?UserInterface $adherent, bool $adherentInstanceChecked): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);

        if ($adherentInstanceChecked) {
            $token->expects($this->once())
                ->method('getUser')
                ->willReturn($adherent)
            ;
        } else {
            $token->expects($this->never())
                ->method('getUser')
            ;
        }

        return $token;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Adherent
     */
    protected function createAdherentMock(): Adherent
    {
        $adherent = $this->createMock(Adherent::class);

        return $adherent;
    }
}
