<?php

namespace App\Mailer\Message;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Election;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Ramsey\Uuid\Uuid;

final class VotingPlatformElectionSecondRoundNotificationMessage extends Message
{
    /**
     * @param Adherent[] $adherents
     */
    public static function create(Election $election, array $adherents, string $url): self
    {
        $first = array_shift($adherents);

        $daysLeft = (int) $election->getDesignation()->getAdditionalRoundDuration();

        $message = new self(
            Uuid::uuid4(),
            $first->getEmailAddress(),
            $first->getFullName(),
            "[Désignations] Vous avez ${daysLeft} jours pour voter à nouveau.",
            [
                'name' => static::escape($election->getElectionEntity()->getName()),
                'days_left' => $daysLeft,
                'is_copol' => DesignationTypeEnum::COPOL === $election->getDesignationType(),
                'second_round_end_date' => static::formatDate($election->getSecondRoundEndDate(), 'EEEE d MMMM y, HH\'h\'mm'),
                'page_url' => $url,
            ],
            [
                'first_name' => $first->getFirstName(),
            ]
        );

        foreach ($adherents as $adherent) {
            $message->addRecipient($adherent->getEmailAddress(), $adherent->getFullName(), [
                'first_name' => $adherent->getFirstName(),
            ]);
        }

        return $message;
    }
}
