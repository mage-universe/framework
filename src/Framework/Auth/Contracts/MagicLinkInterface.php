<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Contracts;

use Closure;
use DateInterval;
use Psr\Http\Message\ServerRequestInterface;

interface MagicLinkInterface
{
    public function queueMagicLinkToEmail(
        ServerRequestInterface $request,
        UserEntityInterface $user,
        string $url,
        string $validateRouteName,
        DateInterval $ttl,
        Closure $mail
    ): void;
    
    public function validateMagicLinkToken(ServerRequestInterface $request): ServerRequestInterface;
}
