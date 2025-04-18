<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Grants;

use Closure;
use DateInterval;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\RequestEvent;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Mage\Framework\Auth\Contracts\LockInterface;
use Mage\Framework\Auth\Contracts\PassportTokensInterface;
use Mage\Framework\Auth\Contracts\TwoFactorTokenInterface;
use Mage\Framework\Auth\Contracts\UserRepositoryInterface;
use Mage\Framework\Auth\Exceptions\AuthException;
use Mage\Framework\Auth\Repositories\User;
use Mage\Framework\Auth\Repositories\UserRepository;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 * @psalm-suppress MixedArgument
 * @property UserRepository $userRepository
 */
class PasswordAuthGrant extends PasswordGrant
{
    public function __construct(
        UserRepositoryInterface $userRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        AuthorizationServer $server,
        PassportTokensInterface $token,
        private readonly LockInterface $lock,
        private readonly TwoFactorTokenInterface $twoFactor,
    ) {
        parent::__construct($userRepository, $refreshTokenRepository, $server, $token);
    }

    public function respondToTwoFactorRequest(ServerRequestInterface $request, DateInterval $ttl): array
    {
        $client = $this->validateClient($request);
        /** @psalm-var User $user */
        $user = $this->validateUser($request, $client);
        return $this->twoFactor->twoFactorTemporalToken($request, $user, $ttl);
    }

    public function respondToTwoFactorValidatorRequest(ServerRequestInterface $request): ResponseTypeInterface
    {
        $request = $this->twoFactor->validateTwoFactorToken($request);
        return $this->respondToAccessTokenRequest($request, new BearerTokenResponse(), $this->accessTokenTTL());
    }

    public function queueTwoFactorEmail(ServerRequestInterface $request, DateInterval $ttl, Closure $mail): void
    {
        $this->twoFactor->queueTwoFactorTokenToEmail($request, $ttl, $mail);
    }

    public function queueForgotPasswordEmail(
        ServerRequestInterface $request,
        string $url,
        string $resetRouteName,
        DateInterval $tokenTTL,
        Closure $mail
    ): void {
        $client = $this->validateClient($request);
        $this->userRepository->queueForgotPasswordTokenToEmail(
            $this->getRequestParameter('username', $request) ?? '',
            $client,
            $url,
            $resetRouteName,
            $tokenTTL,
            $mail
        );
    }

    public function resetPassword(ServerRequestInterface $request): void
    {
        $client = $this->validateClient($request);
        /** @psalm-var int $userId */
        $userId = $this->userRepository->resetPassword(
            $this->getRequestParameter('token', $request) ?? '',
            $this->getRequestParameter('username', $request) ?? '',
            $this->getRequestParameter('password', $request) ?? '',
            $client
        );
        $this->token->removeTokens($userId, $client->getIdentifier());
    }

    protected function validateUser(ServerRequestInterface $request, ClientEntityInterface $client): UserEntityInterface
    {
        $username = $this->getRequestParameter('username', $request);
        $password = $this->getRequestParameter('password', $request);

        if (!\is_string($username) || !\is_string($password)) {
            throw AuthException::invalidCredentials();
        }

        if ($this->lock->isLocked($username)) {
            $this->lock->lock($username);
            throw AuthException::lockedAccount();
        }

        $user = $this->userRepository->getUserEntityByUserCredentials(
            $username,
            $password,
            $this->getIdentifier(),
            $client
        );

        if ($user instanceof UserEntityInterface === false) {
            $this->lock->incrementTries($username);
            $this->getEmitter()->emit(new RequestEvent(RequestEvent::USER_AUTHENTICATION_FAILED, $request));
            throw AuthException::invalidCredentials();
        }
        $this->lock->resetLock($username);
        return $user;
    }

    public function setLockable(bool $isLockable): self
    {
        $this->lock->setLockable($isLockable);
        return $this;
    }

    public function setLockTTL(DateInterval $lockTTL): self
    {
        $this->lock->setLockTTL($lockTTL);
        return $this;
    }

    public function setMaxTries(int $maxTries): self
    {
        $this->lock->setMaxTries($maxTries);
        return $this;
    }

    public function setTriesTTL(DateInterval $triesTTL): self
    {
        $this->lock->setTriesTTL($triesTTL);
        return $this;
    }
}
