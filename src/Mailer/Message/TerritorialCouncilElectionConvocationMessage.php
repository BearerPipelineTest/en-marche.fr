<?php

namespace App\Mailer\Message;

use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use Ramsey\Uuid\Uuid;

final class TerritorialCouncilElectionConvocationMessage extends Message
{
    public static function create(
        TerritorialCouncil $territorialCouncil,
        array $memberships,
        string $territorialCouncilUrl,
        TerritorialCouncilMembership $president
    ): self {
        $election = $territorialCouncil->getCurrentElection();
        $designation = $election->getDesignation();

        $first = array_shift($memberships);
        $adherent = $first->getAdherent();

        $message = new self(
            Uuid::uuid4(),
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            sprintf('[Désignations] Convocation au Conseil territorial du %s', self::dateToString($election->getMeetingStartDate())),
            [
                'territorial_council_name' => $territorialCouncil->getName(),
                'now' => self::formatDate(new \DateTime(), 'EEEE d MMMM y'),
                'territorial_council_url' => $territorialCouncilUrl,
                'vote_start_date' => self::formatDate($designation->getVoteStartDate(), 'EEEE d MMMM y à HH\'h\'mm'),
                'vote_end_date' => self::dateToString($designation->getVoteEndDate()),
                'address' => $election->getInlineFormattedAddress(),
                'meeting_start_date' => self::dateToString($election->getMeetingStartDate()),
                'description' => $election->getDescription(),
                'questions' => $election->getQuestions() ?? 'null',
                'referent_first_name' => $president->getAdherent()->getFirstName(),
                'referent_last_name' => $president->getAdherent()->getLastName(),
                'online_mode' => $election->isOnlineMode(),
                'president_email' => $president->getAdherent()->getEmailAddress(),
                'meeting_url' => $election->getMeetingUrl(),
            ],
            [
                'first_name' => $adherent->getFirstName(),
                'last_name' => $adherent->getLastName(),
            ]
        );

        /** @var TerritorialCouncilMembership[] $memberships */
        foreach ($memberships as $membership) {
            $adherent = $membership->getAdherent();
            $message->addRecipient(
                $adherent->getEmailAddress(),
                $adherent->getFullName(),
                [
                    'first_name' => $adherent->getFirstName(),
                    'last_name' => $adherent->getLastName(),
                ]
            );
        }

        return $message;
    }
}
