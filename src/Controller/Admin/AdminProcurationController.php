<?php

namespace App\Controller\Admin;

use App\Entity\ProcurationRequest;
use App\Procuration\ProcurationManager;
use App\Procuration\ProcurationRequestSerializer;
use App\Repository\ProcurationRequestRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/procuration")
 * @Security("is_granted('ROLE_ADMIN_PROCURATIONS')")
 */
class AdminProcurationController extends AbstractController
{
    /**
     * @Route("/export", methods={"GET"})
     */
    public function exportMailsAction(
        ProcurationRequestRepository $repository,
        ProcurationRequestSerializer $serializer
    ): Response {
        return new Response($serializer->serialize($repository->findAllForExport()), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="procurations-matched.csv"',
        ]);
    }

    /**
     * List the procuration referents invitations URLs.
     *
     * @Route("/request/{id}/deassociate", name="app_admin_procuration_request_deassociate", methods={"GET", "POST"})
     */
    public function deassociateAction(
        Request $sfRequest,
        ProcurationRequest $request,
        ProcurationManager $manager
    ): Response {
        if (!$request->getFoundProxy()) {
            return $this->redirectAfterDeassociation($sfRequest);
        }

        $form = $this->createForm(FormType::class)
            ->add('submit', SubmitType::class)
            ->handleRequest($sfRequest)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->unprocessProcurationRequest($request);

            return $this->redirectAfterDeassociation($sfRequest);
        }

        return $this->render('admin/procuration/request_deassociate.html.twig', [
            'form' => $form->createView(),
            'request' => $request,
        ]);
    }

    private function redirectAfterDeassociation(Request $request)
    {
        if ('proxies' === $request->query->get('from')) {
            return $this->redirectToRoute('admin_app_procurationproxy_list');
        }

        return $this->redirectToRoute('admin_app_procurationrequest_list');
    }
}
