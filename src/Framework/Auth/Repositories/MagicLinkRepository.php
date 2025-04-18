<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Repositories;

use Closure;
use DateInterval;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Mage\Framework\Auth\Contracts\MagicLinkInterface;
use Mage\Framework\Auth\Contracts\UserEntityInterface;
use Psr\Http\Message\ServerRequestInterface;

class MagicLinkRepository extends AuthTokenRepository implements MagicLinkInterface
{
    public function queueMagicLinkToEmail(
        ServerRequestInterface $request,
        UserEntityInterface $user,
        string $url,
        string $validateRouteName,
        DateInterval $ttl,
        Closure $mail
    ): void {
        if (is_null($user->getEmail())) {
            return;
        }

        /** @psalm-var array{expires_in: int, token: string} $token */
        $token = $this->token($request, $user, $ttl, 'auth.magic-link.request.');
        $link = URL::temporarySignedRoute(
            $validateRouteName,
            $token['expires_in'],
            [
                'token' => $token['token'],
                'email' => $user->getEmail(),
            ]
        );

        /** @psalm-var array{query: string} $parsedUrl */
        $parsedUrl = \Safe\parse_url($link);
        $link = $url . '?' . $parsedUrl['query'];

        /** @psalm-var Mailable $mailable */
        $mailable = $mail($link);
        Mail::to($user->getEmail())->queue($mailable);
    }

    public function validateMagicLinkToken(ServerRequestInterface $request): ServerRequestInterface
    {
        $token = $this->validateToken($request);
        [$oRequest] = $this->originalRequestData('auth.magic-link.request.' . $token);
        $this->cacheForget('auth.magic-link.request.' . $token);
        return $oRequest;
    }
}
