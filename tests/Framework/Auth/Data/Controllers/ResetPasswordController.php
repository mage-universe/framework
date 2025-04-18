<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Data\Controllers;

use Mage\Framework\Auth\Http\Requests\ResetPassword\ResetPasswordForgotRequest;
use Mage\Framework\Auth\Http\Requests\ResetPassword\ResetPasswordResetRequest;
use Symfony\Component\HttpFoundation\Response;

class ResetPasswordController extends \Mage\Framework\Auth\Http\Controllers\ResetPasswordController
{
    public function forgot(ResetPasswordForgotRequest $request): Response
    {
        return parent::_forgot($request);
    }

    public function reset(ResetPasswordResetRequest $request): Response
    {
        return parent::_reset($request);
    }

    protected function clientId(): int
    {
        return 1;
    }

    protected function clientSecret(): string
    {
        return 'YIohKpRaizpUPokxEEiqZpe6hCea7fv4kTkK2BLR';
    }

    public function resetRouteName(): string
    {
        return 'auth.reset-password.reset';
    }
}
