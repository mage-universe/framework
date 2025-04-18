<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Data\Controllers;

class MagicLinkCookieController extends MagicLinkController
{
    protected function responseAsCookie(): bool
    {
        return true;
    }
}
