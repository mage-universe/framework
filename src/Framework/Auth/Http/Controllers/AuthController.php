<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Http\Controllers;

abstract class AuthController
{
    abstract protected function clientId(): int;
    abstract protected function clientSecret(): string;

    protected function responseAsCookie(): bool
    {
        return false;
    }

    protected function singleLogin(): bool
    {
        return false;
    }

    protected function accessTokenTTL(): string
    {
        return '1 year';
    }

    protected function refreshTokenTTL(): string
    {
        return '2 years';
    }

    protected function extraData(): array
    {
        return [];
    }
}
