<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Repositories;

use Closure;
use DateInterval;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Mage\Framework\Auth\Contracts\TwoFactorTokenInterface;
use Mage\Framework\Auth\Contracts\UserEntityInterface;
use Mage\Framework\Auth\Exceptions\AuthException;
use Psr\Http\Message\ServerRequestInterface;
use RobThree\Auth\TwoFactorAuth;

final class TwoFactorTokenRepository extends AuthTokenRepository implements TwoFactorTokenInterface
{
    private const int CODE_LENGTH = 6;
    private const int CODE_MIN = 0;
    private const int CODE_MAX = 999999;

    public function twoFactorTemporalToken(
        ServerRequestInterface $request,
        UserEntityInterface $user,
        DateInterval $ttl
    ): array {
        return [
            ...$this->token($request, $user, $ttl, 'auth.two-steps.request.'),
            'types' => array_filter([
                !$user->getIsTwoFactorEmailEnabled() ? null : 'email',
                !$user->getIsTwoFactorAppCodeEnabled() ? null : 'code',
            ]),
        ];
    }

    public function validateTwoFactorToken(ServerRequestInterface $request): ServerRequestInterface
    {
        $request = $this->validateType($request);
        [$oRequest, $user] = $this->originalRequestData('auth.two-steps.request.' . $request['token']);

        $this->validateAppCode($request, $user);
        $this->validateEmailCode($request, $user);

        $this->cacheForget('auth.two-steps.request.' . $request['token']);
        return $oRequest;
    }

    public function queueTwoFactorTokenToEmail(ServerRequestInterface $request, DateInterval $ttl, Closure $mail): void
    {
        $token = $this->validateToken($request);
        [, $user] = $this->originalRequestData('auth.two-steps.request.' . $token);

        $userEmail = $user->getEmail();

        $code = $this->getRandomTwoFactorCode();
        $this->cachePut('auth.two-steps.email.' . $userEmail, $code, $ttl);

        /** @psalm-var Mailable $mailable */
        $mailable = $mail($code);
        Mail::to($userEmail)->queue($mailable);
    }

    /** @psalm-param array{token: string, type: string, code: string } $request */
    private function validateAppCode(array $request, User $user): void
    {
        if ($request['type'] === 'code') {
            $tfa = new TwoFactorAuth();
            $isValid = $tfa->verifyCode($user->getTwoFactorSecret() ?? '', $request['code'] ?? '');
            if (!$isValid) {
                throw AuthException::invalidTwoFactorCode();
            }
        }
    }

    /** @psalm-param array{token: string, type: string, code: string } $request */
    private function validateEmailCode(array $request, User $user): void
    {
        if ($request['type'] === 'email') {
            /** @psalm-var string $userEmail */
            $userEmail = $user->getEmail();
            /** @psalm-var string|null $code */
            $code = $this->cacheGet('auth.two-steps.email.' . $userEmail);
            if (is_null($code) || $code !== $request['code']) {
                throw AuthException::invalidTwoFactorCode();
            }
            $this->cacheForget('auth.two-steps.email.' . $userEmail);
        }
    }

    /** @psalm-return array{token: string, type: string, code: string} */
    private function validateType(ServerRequestInterface $request): array
    {
        $parsedRequest = $request->getParsedBody();
        if (!in_array($parsedRequest['type'] ?? null, ['email', 'code'])) {
            throw AuthException::invalidType();
        }
        /** @psalm-var array{token: string, type: string, code: string} */
        return $parsedRequest;
    }

    private function getRandomTwoFactorCode(): string
    {
        return str_pad((string) random_int(self::CODE_MIN, self::CODE_MAX), self::CODE_LENGTH, '0', STR_PAD_LEFT);
    }
}
