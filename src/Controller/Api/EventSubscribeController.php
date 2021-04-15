<?php

namespace App\Controller\Api;

use App\Entity\Event\BaseEvent;
use App\Event\EventRegistrationCommand;
use App\Event\EventRegistrationFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EventSubscribeController extends AbstractController
{
    private $entityManager;
    private $eventRegistrationFactory;
    private $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventRegistrationFactory $eventRegistrationFactory,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->eventRegistrationFactory = $eventRegistrationFactory;
        $this->validator = $validator;
    }

    public function __invoke(BaseEvent $event, UserInterface $adherent): Response
    {
        $errors = $this->validator->validate($command = new EventRegistrationCommand($event, $adherent));

        if ($errors->count()) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($this->eventRegistrationFactory->createFromCommand($command));
        $this->entityManager->flush();

        return $this->json('OK', Response::HTTP_CREATED);
    }
}