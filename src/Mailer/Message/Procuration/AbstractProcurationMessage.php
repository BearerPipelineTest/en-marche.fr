<?php

namespace App\Mailer\Message\Procuration;

use App\Mailer\Message\Message;

abstract class AbstractProcurationMessage extends Message
{
    protected static function updateSenderInfo(Message $message): Message
    {
        $message->setSenderEmail('ne-pas-repondre@avecvous.fr');
        $message->setSenderName('Procuration avec vous');

        return $message;
    }
}
