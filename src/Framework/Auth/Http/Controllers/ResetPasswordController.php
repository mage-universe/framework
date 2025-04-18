<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Http\Controllers;

use Closure;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Mage\Framework\Auth\Http\Requests\ResetPassword\ResetPasswordForgotRequest;
use Mage\Framework\Auth\Http\Requests\ResetPassword\ResetPasswordResetRequest;
use Mage\Framework\Auth\Mail\ResetPasswordMail;
use Mage\Framework\Auth\ResetPasswordAuth;
use Mage\Framework\Http\Middlewares\InvalidSignature\ValidateSignature;
use Symfony\Component\HttpFoundation\Response;

abstract class ResetPasswordController implements HasMiddleware
{
    public function __construct(protected ResetPasswordAuth $auth)
    {
        $this->auth
            ->setLinkUrl($this->linkUrl())
            ->setResetRouteName($this->resetRouteName())
            ->setLinkTTL($this->linkTTL())
            ->setLinkMail($this->linkMail());
    }
    public static function middleware(): array
    {
        return [new Middleware(ValidateSignature::class, only: ['reset'])];
    }

    abstract protected function clientId(): int;
    abstract protected function clientSecret(): string;
    abstract protected function resetRouteName(): string;

    protected function _forgot(ResetPasswordForgotRequest $request): Response
    {
        return $this->auth->issueForgotPasswordTokenToEmail([
            'client_id' => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            ...$request->validated(),
        ]);
    }

    protected function _reset(ResetPasswordResetRequest $request): Response
    {
        return $this->auth->setNewPassword([
            'client_id' => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            ...$request->validated(),
        ]);
    }

    protected function linkUrl(): string
    {
        return route($this->resetRouteName());
    }

    protected function linkTTL(): string
    {
        /** @psalm-var string $expires */
        $expires = config('auth.passwords.users.expire');
        return $expires . ' minutes';
    }

    protected function linkMail(): Closure
    {
        return fn (string $link) => new ResetPasswordMail($link);
    }
}
