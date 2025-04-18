<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Data\Controllers;

use Closure;
use Tests\Framework\Auth\Data\Mail\MagicLinkMailVariation;

class NonDefaultMagicLinkController extends MagicLinkController
{
    protected function clientId(): int
    {
        return 1;
    }

    protected function clientSecret(): string
    {
        return 'YIohKpRaizpUPokxEEiqZpe6hCea7fv4kTkK2BLR';
    }

    protected function linkUrl(): string
    {
        return 'http://localhost';
    }

    public function validateRouteName(): string
    {
        return 'auth.magic-link.validate2';
    }

    protected function setLinkTTL(): string
    {
        return '30 minutes';
    }

    protected function linkMail(): Closure
    {
        return fn (string $link) => new MagicLinkMailVariation($link);
    }
}
