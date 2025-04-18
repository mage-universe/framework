<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Grants;

use DateInterval;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\RequestAccessTokenEvent;
use League\OAuth2\Server\RequestEvent;
use League\OAuth2\Server\RequestRefreshTokenEvent;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Mage\Framework\Auth\Contracts\PassportTokensInterface;
use Mage\Framework\Auth\Contracts\UserRepositoryInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 * @psalm-suppress MixedArgument
 */
abstract class PasswordGrant extends \League\OAuth2\Server\Grant\PasswordGrant
{
    private DateInterval $accessTokenTTL;
    private bool $isSingleLogin;

    public function __construct(
        UserRepositoryInterface $userRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        private readonly AuthorizationServer $server,
        protected readonly PassportTokensInterface $token
    ) {
        parent::__construct($userRepository, $refreshTokenRepository);
        $this->isSingleLogin = false;
    }

    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        DateInterval $accessTokenTTL
    ): ResponseTypeInterface {
        $client = $this->validateClient($request);
        $scopes = $this->validateScopes($this->getRequestParameter('scope', $request, $this->defaultScope));
        $user = $this->validateUser($request, $client);

        if ($this->isSingleLogin) {
            $this->token->removeTokens($user->getIdentifier(), $client->getIdentifier());
        }

        $finalizedScopes = $this->scopeRepository->finalizeScopes(
            $scopes,
            $this->getIdentifier(),
            $client,
            $user->getIdentifier()
        );

        $accessToken = $this->issueAccessToken($accessTokenTTL, $client, $user->getIdentifier(), $finalizedScopes);
        $this->getEmitter()->emit(
            new RequestAccessTokenEvent(RequestEvent::ACCESS_TOKEN_ISSUED, $request, $accessToken)
        );
        $responseType->setAccessToken($accessToken);
        $responseType->setEncryptionKey($this->encryptionKey);

        $refreshToken = $this->issueRefreshToken($accessToken);

        if ($refreshToken !== null) {
            $this->getEmitter()->emit(
                new RequestRefreshTokenEvent(RequestEvent::REFRESH_TOKEN_ISSUED, $request, $refreshToken)
            );
            $responseType->setRefreshToken($refreshToken);
        }
        return $responseType;
    }

    public function accessTokenTTL(): DateInterval
    {
        return $this->accessTokenTTL;
    }

    public function setAccessTokenTTL(DateInterval $accessTokenTTL): self
    {
        $this->accessTokenTTL = $accessTokenTTL;
        $this->server->enableGrantType($this, $this->accessTokenTTL);
        return $this;
    }

    public function setSingleLogin(bool $singleLogin): self
    {
        $this->isSingleLogin = $singleLogin;
        return $this;
    }
}
