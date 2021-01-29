<?php

namespace App\Controller;

use App\React\ReactAppRegistry;
use App\Security\Http\Session\AnonymousFollowerSession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ReactController extends AbstractController
{
    public function __invoke(
        ReactAppRegistry $registry,
        Request $request,
        AnonymousFollowerSession $anonymousFollowerSession
    ): Response {
        $app = $registry->getApp($request->attributes->get('_react_app'));

        if (!$app) {
            throw $this->createNotFoundException('App not found');
        }

        if (!$app->enableInProduction() && !$this->getParameter('enable_canary')) {
            throw $this->createNotFoundException('This app is disabled in production.');
        }

        $manifest = $registry->readManifest($app);

        if (!$manifest) {
            throw $this->createNotFoundException('Manifest not found, does the app exist and has been built?');
        }

        if ($this->isGranted('IS_ANONYMOUS') && $authenticate = $anonymousFollowerSession->start($request)) {
            return $authenticate;
        }

        return $this->render('react.html.twig', ['reactApp' => $app, 'manifest' => $manifest]);
    }
}
