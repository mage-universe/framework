<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Issuers;

use Closure;
use DateInterval;
use Mage\Framework\Auth\Grants\PasswordAuthGrant;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Response;

final class TwoFactorAuthIssuer extends Issuer
{
    public function __construct(
        private readonly ServerRequestInterface $request,
        private readonly PasswordAuthGrant $grant
    ) {}

    public function token(DateInterval $ttl): false|Response
    {
        $response = $this->grant->respondToTwoFactorRequest($this->request, $ttl);
        if (empty($response['types'])) {
            return false;
        }
        /** @psalm-var Response */
        return response()->json($response);
    }

    public function issue(array $extraData, bool $responseAsCookie): Response
    {
        $response = $this->grant->respondToTwoFactorValidatorRequest($this->request);
        return $this->bearer($response, $this->grant->accessTokenTTL(), $extraData, $responseAsCookie);
    }

    public function email(DateInterval $ttl, Closure $mail): Response
    {
        $this->grant->queueTwoFactorEmail($this->request, $ttl, $mail);
        /** @psalm-var Response */
        return response()->noContent();
    }
}
