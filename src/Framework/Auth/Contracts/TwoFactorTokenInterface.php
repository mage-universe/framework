<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Contracts;

use Closure;
use DateInterval;
use Psr\Http\Message\ServerRequestInterface;

interface TwoFactorTokenInterface
{
    public function twoFactorTemporalToken(
        ServerRequestInterface $request,
        UserEntityInterface $user,
        DateInterval $ttl
    ): array;
    public function validateTwoFactorToken(ServerRequestInterface $request): ServerRequestInterface;
    public function queueTwoFactorTokenToEmail(ServerRequestInterface $request, DateInterval $ttl, Closure $mail): void;
}
