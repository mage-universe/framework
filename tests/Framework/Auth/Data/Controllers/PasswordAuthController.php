<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Data\Controllers;

use Mage\Framework\Auth\Http\Requests\Password\PasswordEmailRequest;
use Mage\Framework\Auth\Http\Requests\Password\PasswordLoginRequest;
use Mage\Framework\Auth\Http\Requests\Password\PasswordTFARequest;
use Symfony\Component\HttpFoundation\Response;

class PasswordAuthController extends \Mage\Framework\Auth\Http\Controllers\PasswordAuthController
{
    public function login(PasswordLoginRequest $request): Response
    {
        return parent::_login($request);
    }

    public function tfa(PasswordTFARequest $request): Response
    {
        return parent::_tfa($request);
    }

    public function email(PasswordEmailRequest $request): Response
    {
        return parent::_email($request);
    }

    protected function clientId(): int
    {
        return 1;
    }

    protected function clientSecret(): string
    {
        return 'YIohKpRaizpUPokxEEiqZpe6hCea7fv4kTkK2BLR';
    }
}
