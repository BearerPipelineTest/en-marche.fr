<?php

namespace App\OAuth\AuthorizationValidators;

use App\OAuth\Store\AccessTokenStore;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\ValidationData;
use League\OAuth2\Server\AuthorizationValidators\AuthorizationValidatorInterface;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\CryptTrait;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ServerRequestInterface;

class JsonWebTokenValidator implements AuthorizationValidatorInterface
{
    use CryptTrait;

    /**
     * @var CryptKey
     */
    private $publicKey;
    private $accessTokenRepository;
    private $enableQueryStringTransport;

    public function __construct(AccessTokenStore $accessTokenRepository, bool $enableQueryStringTransport = false)
    {
        $this->accessTokenRepository = $accessTokenRepository;
        $this->enableQueryStringTransport = $enableQueryStringTransport;
    }

    public function setPublicKey(CryptKey $key)
    {
        $this->publicKey = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthorization(ServerRequestInterface $request)
    {
        try {
            // Attempt to parse and validate the JWT
            $token = (new Parser())->parse($this->readAccessToken($request));
            if (false === $token->verify(new Sha256(), $this->publicKey->getKeyPath())) {
                throw OAuthServerException::accessDenied('Access token could not be verified');
            }

            // Ensure access token hasn't expired
            $data = new ValidationData();
            $data->setCurrentTime(time());

            if (false === $token->validate($data)) {
                throw OAuthServerException::accessDenied('Access token is invalid');
            }

            // Check if token has been revoked
            $accessToken = $this->accessTokenRepository->findAccessToken($token->getClaim('jti'));
            if (!$accessToken || $accessToken->isRevoked()) {
                throw OAuthServerException::accessDenied('Access token has been revoked');
            }

            $device = $accessToken->getDevice();

            // Return the request with additional attributes
            return $request
                ->withAttribute('oauth_access_token_id', $token->getClaim('jti'))
                ->withAttribute('oauth_client_id', $token->getClaim('aud'))
                ->withAttribute('oauth_user_id', $token->getClaim('sub'))
                ->withAttribute('oauth_scopes', $token->getClaim('scopes'))
                ->withAttribute('oauth_device_id', $device ? $device->getIdentifier() : null)
            ;
        } catch (\InvalidArgumentException $exception) {
            // JWT couldn't be parsed so return the request as is
            throw OAuthServerException::accessDenied($exception->getMessage());
        } catch (\RuntimeException $exception) {
            //JWR couldn't be parsed so return the request as is
            throw OAuthServerException::accessDenied('Error while decoding to JSON');
        }
    }

    private function readAccessToken(ServerRequestInterface $request): string
    {
        // Read access token from Authorization request header
        if (1 === \count($header = $request->getHeader('authorization'))) {
            return trim(preg_replace('/^(?:\s+)?Bearer\s/', '', $header[0]));
        }

        if (!$this->enableQueryStringTransport) {
            throw OAuthServerException::accessDenied('Missing "Authorization" header');
        }

        // Otherwise, read access token from the query string if it's allowed
        $params = $request->getQueryParams();
        if (empty($params['access_token'])) {
            throw OAuthServerException::accessDenied('Missing "Authorization" header or "access_token" query string parameter.');
        }

        return $params['access_token'];
    }
}
