<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Issuers;

use Closure;
use DateInterval;
use Mage\Framework\Auth\Grants\PasswordLessGrant;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Response;

final class MagicLinkIssuer extends Issuer
{
    public function __construct(
        private readonly ServerRequestInterface $request,
        private readonly PasswordLessGrant $grant
    ) {}

    public function email(string $url, string $validateRouteName, DateInterval $ttl, Closure $mail): Response
    {
        $this->grant->respondToMagicLinkTokenRequest($this->request, $url, $validateRouteName, $ttl, $mail);
        /** @psalm-var Response */
        return response()->noContent();
    }

    public function issue(array $extraData, bool $responseAsCookie): Response
    {
        $response = $this->grant->respondToMagicLinkTokenValidateRequest($this->request);
        return $this->bearer($response, $this->grant->accessTokenTTL(), $extraData, $responseAsCookie);
    }
}
