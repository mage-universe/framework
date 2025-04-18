<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Data\Controllers;

use Closure;
use Mage\Framework\Auth\Http\Requests\Password\PasswordEmailRequest;
use Mage\Framework\Auth\Http\Requests\Password\PasswordLoginRequest;
use Mage\Framework\Auth\Http\Requests\Password\PasswordTFARequest;
use Symfony\Component\HttpFoundation\Response;
use Tests\Framework\Auth\Data\Mail\TwoFactorEmailVariation;

class NonDefaultPasswordAuth2Controller extends \Mage\Framework\Auth\Http\Controllers\PasswordAuthController
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

    protected function isLockable(): bool
    {
        return false;
    }

    protected function lockTTL(): string
    {
        return '10 minutes';
    }

    protected function maxTries(): int
    {
        return 2;
    }

    protected function triesTTL(): string
    {
        return '15 seconds';
    }

    protected function tfaEnabled(): bool
    {
        return true;
    }

    protected function tfaTTL(): string
    {
        return '30 minutes';
    }

    protected function tfaMail(): Closure
    {
        return fn (string $code) => new TwoFactorEmailVariation($code);
    }
}
