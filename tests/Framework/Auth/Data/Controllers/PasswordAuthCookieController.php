<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Data\Controllers;

class PasswordAuthCookieController extends PasswordAuthController
{
    protected function tfaEnabled(): bool
    {
        return false;
    }

    protected function responseAsCookie(): bool
    {
        return true;
    }
}
