<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Issuers;

use Closure;
use DateInterval;
use Mage\Framework\Auth\Grants\PasswordAuthGrant;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Response;

final readonly class ForgotPasswordIssuer
{
    public function __construct(
        private ServerRequestInterface $request,
        private PasswordAuthGrant $passwordAuthGrant
    ) {}

    public function email(string $url, string $resetRouteName, DateInterval $ttl, Closure $mail): Response
    {
        $this->passwordAuthGrant->queueForgotPasswordEmail($this->request, $url, $resetRouteName, $ttl, $mail);
        return response()->noContent();
    }

    public function reset(): Response
    {
        $this->passwordAuthGrant->resetPassword($this->request);
        return response()->noContent();
    }
}
