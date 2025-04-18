<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Grants;

use Closure;
use DateInterval;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Mage\Framework\Auth\Contracts\MagicLinkInterface;
use Mage\Framework\Auth\Contracts\PassportTokensInterface;
use Mage\Framework\Auth\Contracts\UserEntityInterface;
use Mage\Framework\Auth\Contracts\UserRepositoryInterface;
use Mage\Framework\Auth\Exceptions\AuthException;
use Mage\Framework\Auth\Repositories\User;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 * @psalm-suppress MixedArgument
 */
class PasswordLessGrant extends PasswordGrant
{
    public function __construct(
        UserRepositoryInterface $userRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        AuthorizationServer $server,
        PassportTokensInterface $token,
        private readonly MagicLinkInterface $magicLink,
    ) {
        parent::__construct($userRepository, $refreshTokenRepository, $server, $token);
    }

    public function respondToMagicLinkTokenRequest(
        ServerRequestInterface $request,
        string $url,
        string $validateRouteName,
        DateInterval $ttl,
        Closure $mail
    ): void {
        $client = $this->validateClient($request);
        /** @psalm-var User $user */
        $user = $this->validateUser($request, $client);
        $this->magicLink->queueMagicLinkToEmail($request, $user, $url, $validateRouteName, $ttl, $mail);
    }

    public function respondToMagicLinkTokenValidateRequest(ServerRequestInterface $request): ResponseTypeInterface
    {
        $request = $this->magicLink->validateMagicLinkToken($request);
        return $this->respondToAccessTokenRequest($request, new BearerTokenResponse(), $this->accessTokenTTL());
    }

    protected function validateUser(ServerRequestInterface $request, ClientEntityInterface $client): UserEntityInterface
    {
        $username = $this->getRequestParameter('username', $request);
        if (!\is_string($username)) {
            throw AuthException::invalidCredentials();
        }

        /** @psalm-var UserRepositoryInterface $user */
        $user = $this->userRepository;

        /** @psalm-var UserEntityInterface */
        return $user->getUserEntityByUserName($username, $this->getIdentifier(), $client);
    }
}
