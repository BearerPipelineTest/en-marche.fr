<?php

namespace App\Controller\EnMarche;

use App\CitizenAction\CitizenActionManager;
use App\CitizenAction\CitizenActionRegistrationCommandHandler;
use App\Controller\EntityControllerTrait;
use App\Entity\Event\CitizenAction;
use App\Event\EventRegistrationCommand;
use App\Event\EventRegistrationManager;
use App\Exception\BadUuidRequestException;
use App\Exception\InvalidUuidException;
use App\Form\EventRegistrationType;
use App\Repository\CitizenProjectMembershipRepository;
use App\Security\Http\Session\AnonymousFollowerSession;
use Doctrine\ORM\EntityNotFoundException;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/action-citoyenne/{slug}", name="app_citizen_action_event")
 * @Entity("action", expr="repository.findOneCitizenActionBySlug(slug)")
 */
class CitizenActionController extends AbstractController
{
    use EntityControllerTrait;

    /**
     * @Route(name="_show", methods={"GET"})
     */
    public function showAction(CitizenAction $action, CitizenActionManager $citizenActionManager): Response
    {
        return $this->render('citizen_action/show.html.twig', [
            'citizen_action' => $action,
            'participants' => $citizenActionManager->getRegistrations($action),
        ]);
    }

    /**
     * @Route("/inscription-adherent", name="_attend_adherent", methods={"GET"})
     *
     * @Security("is_granted('ROLE_ADHERENT')")
     */
    public function attendAdherentAction(
        CitizenAction $citizenAction,
        UserInterface $adherent,
        ValidatorInterface $validator,
        CitizenActionRegistrationCommandHandler $handler
    ): Response {
        if ($citizenAction->isFinished()) {
            throw $this->createNotFoundException(sprintf('CitizenAction "%s" is finished and does not accept registrations anymore', $citizenAction->getUuid()));
        }

        if ($citizenAction->isCancelled()) {
            throw $this->createNotFoundException(sprintf('CitizenAction "%s" is cancelled and does not accept registrations anymore', $citizenAction->getUuid()));
        }

        if ($citizenAction->isFull()) {
            $this->addFlash('info', 'L\'événement est complet');

            return $this->redirectToRoute('app_citizen_action_event_show', ['slug' => $citizenAction->getSlug()]);
        }

        $command = new EventRegistrationCommand($citizenAction, $adherent);
        $errors = $validator->validate($command);

        if (0 === $errors->count()) {
            $handler->handle($command);
            $this->addFlash('info', 'citizen_action.registration.success');

            return $this->redirectToRoute('app_citizen_action_event_attend_confirmation', [
                'slug' => $citizenAction->getSlug(),
                'registration' => (string) $command->getRegistrationUuid(),
            ]);
        }

        $this->addFlash('info', $errors[0]->getMessage());

        return $this->redirectToRoute('app_citizen_action_event_show', ['slug' => $citizenAction->getSlug()]);
    }

    /**
     * @Route("/inscription", name="_attend", methods={"GET", "POST"})
     */
    public function attendAction(
        Request $request,
        CitizenAction $citizenAction,
        ?UserInterface $adherent,
        CitizenActionRegistrationCommandHandler $handler,
        AnonymousFollowerSession $anonymousFollowerSession
    ): Response {
        if ($adherent) {
            return $this->redirectToRoute('app_citizen_action_event_attend_adherent', ['slug' => $citizenAction->getSlug()]);
        }

        if ($citizenAction->isFinished()) {
            throw $this->createNotFoundException(sprintf('CitizenAction "%s" is finished and does not accept registrations anymore', $citizenAction->getUuid()));
        }

        if ($citizenAction->isCancelled()) {
            throw $this->createNotFoundException(sprintf('CitizenAction "%s" is cancelled and does not accept registrations anymore', $citizenAction->getUuid()));
        }

        if ($citizenAction->isFull()) {
            $this->addFlash('info', 'L\'événement est complet');

            return $this->redirectToRoute('app_citizen_action_event_show', ['slug' => $citizenAction->getSlug()]);
        }

        if ($this->isGranted('IS_ANONYMOUS') && $authenticate = $anonymousFollowerSession->start($request)) {
            return $authenticate;
        }

        $form = $this
            ->createForm(EventRegistrationType::class, $command = new EventRegistrationCommand($citizenAction))
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $handler->handle($command);
            $this->addFlash('info', 'citizen_action.registration.success');

            return $this->redirectToRoute('app_citizen_action_event_attend_confirmation', [
                'slug' => $citizenAction->getSlug(),
                'registration' => (string) $command->getRegistrationUuid(),
            ]);
        }

        return $this->render('citizen_action/attend.html.twig', [
            'citizen_action' => $citizenAction,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/desinscription", name="_unregistration", condition="request.isXmlHttpRequest()", methods={"GET", "POST"})
     * @Security("is_granted('UNREGISTER_CITIZEN_ACTION', citizenAction)")
     */
    public function unregistrationAction(
        Request $request,
        CitizenAction $citizenAction,
        CitizenActionManager $manager
    ): JsonResponse {
        if (!$this->isCsrfTokenValid('event.unregistration', $token = $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF protection token to unregister from the citizen action.');
        }

        try {
            $manager->unregisterFromCitizenAction($citizenAction, $this->getUser());
        } catch (EntityNotFoundException $e) {
            return new JsonResponse(
                ['error' => 'Impossible d\'exécuter la désinscription de l\'action citoyenne, votre inscription n\'est pas trouvée.'],
                Response::HTTP_NOT_FOUND
            );
        }

        return new JsonResponse();
    }

    /**
     * @Route(
     *     path="/confirmation",
     *     name="_attend_confirmation",
     *     condition="request.query.has('registration')",
     *     methods={"GET"}
     * )
     */
    public function attendConfirmationAction(
        Request $request,
        CitizenAction $citizenAction,
        EventRegistrationManager $manager
    ): Response {
        try {
            if (!$registration = $manager->findRegistration($uuid = $request->query->get('registration'))) {
                throw $this->createNotFoundException(sprintf('Unable to find event registration by its UUID: %s', $uuid));
            }
        } catch (InvalidUuidException $e) {
            throw new BadUuidRequestException($e);
        }

        if (!$registration->matches($citizenAction, $this->getUser())) {
            throw $this->createAccessDeniedException('Invalid event registration');
        }

        return $this->render('citizen_action/attend_confirmation.html.twig', [
            'citizen_action' => $citizenAction,
            'registration' => $registration,
        ]);
    }

    /**
     * @Route("/ical", name="_export_ical", methods={"GET"})
     */
    public function exportIcalAction(CitizenAction $citizenAction, SerializerInterface $serializer): Response
    {
        $disposition = sprintf('%s; filename=%s.ics', ResponseHeaderBag::DISPOSITION_ATTACHMENT, $citizenAction->getSlug());
        $response = new Response($serializer->serialize($citizenAction, 'ical'));
        $response->headers->set('Content-Type', 'text/calendar');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * @Route("/participants", name="_list_participants", methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function listParticipantsAction(
        CitizenAction $citizenAction,
        CitizenActionManager $citizenActionManager,
        CitizenProjectMembershipRepository $citizenProjectMembershipRepository
    ): Response {
        $participants = $citizenActionManager->populateRegistrationWithAdherentsInformations(
            $citizenActionManager->getRegistrations($citizenAction),
            $citizenProjectMembershipRepository->findAdministrators($citizenAction->getCitizenProject())
        );

        return $this->render('citizen_action/list_participants.html.twig', [
            'citizen_action' => $citizenAction,
            'participants' => $participants,
        ]);
    }
}
