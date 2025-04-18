<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Issuers;

use DateInterval;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Mage\Framework\Auth\Http\Response\BearerToken;
use Safe\DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

abstract class Issuer
{
    public function bearer(
        ResponseTypeInterface $response,
        DateInterval $ttl,
        array $extraData = [],
        bool $asCookie = false
    ): Response {
        $bearerTokenGenerator = new BearerToken($response);
        $response = $bearerTokenGenerator->token($extraData);

        if ($asCookie) {
            /** @psalm-var Response */
            return response()->noContent()->withCookie(
                cookie('token', encrypt(json_encode($response)), $this->toMinutes($ttl))
            );
        }

        /** @psalm-var Response */
        return response()->json($response);
    }

    private function toMinutes(DateInterval $ttl): int
    {
        $now = (new DateTimeImmutable());
        $a = $now->add($ttl)->getTimestamp() - $now->getTimestamp();
        return intval(floor($a / 60));
    }
}
