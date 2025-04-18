<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Http\Controllers;

use Closure;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Mage\Framework\Auth\Http\Requests\PasswordLess\PasswordLessLoginRequest;
use Mage\Framework\Auth\Http\Requests\PasswordLess\PasswordLessValidateRequest;
use Mage\Framework\Auth\Mail\MagicLinkMail;
use Mage\Framework\Auth\PasswordLessAuth;
use Mage\Framework\Http\Middlewares\InvalidSignature\ValidateSignature;
use Symfony\Component\HttpFoundation\Response;

abstract class PasswordLessAuthController extends AuthController implements HasMiddleware
{
    public function __construct(protected PasswordLessAuth $auth)
    {
        $this->auth
            ->setAccessTokenTTL($this->accessTokenTTL())
            ->setRefreshTokenTTL($this->refreshTokenTTL())
            ->setExtraData($this->extraData())
            ->setResponseAsCookie($this->responseAsCookie())
            ->setSingleLogin($this->singleLogin())
            ->setLinkUrl($this->linkUrl())
            ->setValidateRouteName($this->validateRouteName())
            ->setLinkTTL($this->setLinkTTL())
            ->setLinkMail($this->linkMail());
    }
    public static function middleware(): array
    {
        return [new Middleware(ValidateSignature::class, only: ['validate'])];
    }

    abstract public function validateRouteName(): string;

    protected function _login(PasswordLessLoginRequest $request): Response
    {
        return $this->auth->respondToLoginRequest([
            'client_id' => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            ...$request->validated(),
        ]);
    }

    protected function _validate(PasswordLessValidateRequest $request): Response
    {
        return $this->auth->respondToMagicLinkTokenRequest($request->validated());
    }

    protected function linkUrl(): string
    {
        return route($this->validateRouteName());
    }

    protected function setLinkTTL(): string
    {
        return '5 minutes';
    }

    protected function linkMail(): Closure
    {
        return fn (string $link) => new MagicLinkMail($link);
    }
}
