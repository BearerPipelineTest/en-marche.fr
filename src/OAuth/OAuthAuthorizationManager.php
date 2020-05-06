<?php

namespace App\OAuth;

use App\Entity\Adherent;
use App\Entity\OAuth\Client;
use App\Entity\OAuth\UserAuthorization;
use App\OAuth\Model\Scope;
use App\Repository\OAuth\UserAuthorizationRepository;

class OAuthAuthorizationManager
{
    private $userAuthorizationRepository;

    public function __construct(UserAuthorizationRepository $userAuthorizationRepository)
    {
        $this->userAuthorizationRepository = $userAuthorizationRepository;
    }

    /**
     * @param Scope[] $scopes
     */
    public function isAuthorized(Adherent $user, Client $client, array $scopes): bool
    {
        foreach ($scopes as $scope) {
            if (!$this->isClientSupportsScope($client, $scope)) {
                return false;
            }
        }

        if (!$client->isAskUserForAuthorization()) {
            return true;
        }

        $userAuthorization = $this->userAuthorizationRepository->findByUserAndClient($user, $client);

        if ($userAuthorization && $userAuthorization->supportsScopes($scopes)) {
            return true;
        }

        return false;
    }

    /**
     * @param Scope[] $scopes
     */
    public function record(Adherent $user, Client $client, array $scopes): void
    {
        if (!$client->isAskUserForAuthorization()) {
            return;
        }

        if (!$userAuthorization = $this->userAuthorizationRepository->findByUserAndClient($user, $client)) {
            $userAuthorization = new UserAuthorization(null, $user, $client, $scopes);
        }

        $userAuthorization->setScopes($scopes);

        $this->userAuthorizationRepository->save($userAuthorization);
    }

    private function isClientSupportsScope(Client $client, Scope $scope): bool
    {
        return $client->supportsScope($scope->getIdentifier());
    }
}
