<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Data\Controllers;

use Closure;
use Mage\Framework\Auth\Http\Requests\ResetPassword\ResetPasswordForgotRequest;
use Symfony\Component\HttpFoundation\Response;
use Tests\Framework\Auth\Data\Mail\ResetPasswordMailVariation;
use Tests\Framework\Auth\Data\Request\ResetPasswordResetRequest;

class NonDefaultResetPasswordController extends \Mage\Framework\Auth\Http\Controllers\ResetPasswordController
{
    public static function middleware(): array
    {
        return [];
    }

    protected function clientId(): int
    {
        return 1;
    }

    protected function clientSecret(): string
    {
        return 'YIohKpRaizpUPokxEEiqZpe6hCea7fv4kTkK2BLR';
    }

    public function forgot(ResetPasswordForgotRequest $request): Response
    {
        return parent::_forgot($request);
    }

    public function reset(ResetPasswordResetRequest $request): Response
    {
        return parent::_reset($request);
    }

    public function resetRouteName(): string
    {
        return 'auth.reset-password.reset2';
    }

    protected function linkUrl(): string
    {
        return 'http://localhost/auth/reset';
    }

    protected function linkTTL(): string
    {
        return '2 hours';
    }

    protected function linkMail(): Closure
    {
        return fn (string $link) => new ResetPasswordMailVariation($link);
    }
}
