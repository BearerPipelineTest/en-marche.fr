<?php

namespace App\AdherentMessage\TransactionalMessage\MessageModifier;

use App\Entity\AdherentMessage\AdherentMessageInterface;

interface MessageModifierInterface
{
    public function support(AdherentMessageInterface $message): bool;

    public function modify(AdherentMessageInterface $message): void;
}
