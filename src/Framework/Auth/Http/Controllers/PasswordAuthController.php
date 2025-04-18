<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Http\Controllers;

use Closure;
use Mage\Framework\Auth\Http\Requests\Password\PasswordEmailRequest;
use Mage\Framework\Auth\Http\Requests\Password\PasswordLoginRequest;
use Mage\Framework\Auth\Http\Requests\Password\PasswordTFARequest;
use Mage\Framework\Auth\Mail\TwoFactorEmail;
use Mage\Framework\Auth\PasswordAuth;
use Symfony\Component\HttpFoundation\Response;

abstract class PasswordAuthController extends AuthController
{
    public function __construct(protected PasswordAuth $auth)
    {
        $this->auth
            ->setAccessTokenTTL($this->accessTokenTTL())
            ->setRefreshTokenTTL($this->refreshTokenTTL())
            ->setExtraData($this->extraData())
            ->setResponseAsCookie($this->responseAsCookie())
            ->setSingleLogin($this->singleLogin())
            ->setTfaEnabled($this->tfaEnabled())
            ->setTfaTTL($this->tfaTTL())
            ->setLockable($this->isLockable())
            ->setLockTTL($this->lockTTL())
            ->setMaxTries($this->maxTries())
            ->setTriesTTL($this->triesTTL())
            ->setTfaMail($this->tfaMail());
    }

    protected function _login(PasswordLoginRequest $request): Response
    {
        return $this->auth->respondToLoginRequest([
            'client_id' => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            ...$request->validated(),
        ]);
    }

    protected function _tfa(PasswordTFARequest $request): Response
    {
        return $this->auth->respondToLoginUsingTwoFactorAuthTokenRequest($request->validated());
    }

    protected function _email(PasswordEmailRequest $request): Response
    {
        return $this->auth->issueTwoFactorAuthTokenToEmail($request->validated());
    }

    protected function isLockable(): bool
    {
        return true;
    }

    protected function lockTTL(): string
    {
        return '5 minutes';
    }

    protected function maxTries(): int
    {
        return 5;
    }

    protected function triesTTL(): string
    {
        return '30 seconds';
    }

    protected function tfaEnabled(): bool
    {
        return true;
    }

    protected function tfaTTL(): string
    {
        return '5 minutes';
    }

    protected function tfaMail(): Closure
    {
        return fn (string $code) => new TwoFactorEmail($code);
    }
}
