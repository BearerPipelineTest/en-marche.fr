<?php

namespace App\TerritorialCouncil\Listeners;

use App\Entity\TerritorialCouncil\Candidacy;
use App\Mailer\MailerService;
use App\Mailer\Message\TerritorialCouncilCandidacyInvitationAcceptedMessage;
use App\Mailer\Message\TerritorialCouncilCandidacyInvitationCreatedMessage;
use App\Mailer\Message\TerritorialCouncilCandidacyInvitationDeclinedMessage;
use App\Mailer\Message\TerritorialCouncilCandidacyInvitationRemovedMessage;
use App\TerritorialCouncil\Events;
use App\VotingPlatform\Event\CandidacyInvitationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

class SendCandidacyEmailListener implements EventSubscriberInterface
{
    private $mailer;
    private $urlGenerator;
    private $translator;

    public function __construct(
        MailerService $transactionalMailer,
        TranslatorInterface $translator,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->mailer = $transactionalMailer;
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::CANDIDACY_INVITATION_UPDATE => 'onInvitationUpdate',
            Events::CANDIDACY_INVITATION_DECLINE => 'onInvitationDecline',
            Events::CANDIDACY_INVITATION_ACCEPT => 'onInvitationAccept',
        ];
    }

    public function onInvitationUpdate(CandidacyInvitationEvent $event): void
    {
        $candidacy = $event->getCandidacy();

        if (!$candidacy instanceof Candidacy) {
            return;
        }

        $invitation = $event->getInvitation();
        $previouslyInvitedMembership = $event->getPreviouslyInvitedMembership();

        if ($invitation && (!$previouslyInvitedMembership || $previouslyInvitedMembership !== $invitation->getMembership())) {
            $this->mailer->sendMessage(TerritorialCouncilCandidacyInvitationCreatedMessage::create(
                $invitation->getMembership()->getAdherent(),
                $candidacy->getMembership()->getAdherent(),
                $candidacy->getElection()->getDesignation(),
                $this->translator->trans('territorial_council.membership.quality.'.$candidacy->getQuality()),
                $this->urlGenerator->generate('app_territorial_council_candidature_invitation_list', [], UrlGeneratorInterface::ABSOLUTE_URL)
            ));
        }

        if ($previouslyInvitedMembership && (!$invitation || $previouslyInvitedMembership !== $invitation->getMembership())) {
            $this->mailer->sendMessage(TerritorialCouncilCandidacyInvitationRemovedMessage::create(
                $previouslyInvitedMembership->getAdherent(),
                $candidacy->getMembership()->getAdherent(),
                $candidacy->getElection()->getDesignation(),
                $this->urlGenerator->generate('app_territorial_council_index', [], UrlGeneratorInterface::ABSOLUTE_URL)
            ));
        }
    }

    public function onInvitationDecline(CandidacyInvitationEvent $event): void
    {
        $candidacy = $event->getCandidacy();

        if (!$candidacy instanceof Candidacy) {
            return;
        }

        $invitation = $event->getInvitation();

        $this->mailer->sendMessage(TerritorialCouncilCandidacyInvitationDeclinedMessage::create(
            $invitation->getMembership()->getAdherent(),
            $candidacy->getMembership()->getAdherent(),
            $candidacy->getElection()->getDesignation(),
            $this->urlGenerator->generate('app_territorial_council_candidature_select_pair_candidate', [], UrlGeneratorInterface::ABSOLUTE_URL)
        ));
    }

    public function onInvitationAccept(CandidacyInvitationEvent $event): void
    {
        $candidacy = $event->getCandidacy();

        if (!$candidacy instanceof Candidacy) {
            return;
        }

        $invitation = $event->getInvitation();

        $this->mailer->sendMessage(TerritorialCouncilCandidacyInvitationAcceptedMessage::create(
            $invitation->getMembership()->getAdherent(),
            $candidacy->getMembership()->getAdherent(),
            $candidacy->getElection()->getDesignation(),
            $this->urlGenerator->generate('app_territorial_council_candidacy_list', [], UrlGeneratorInterface::ABSOLUTE_URL)
        ));
    }
}
