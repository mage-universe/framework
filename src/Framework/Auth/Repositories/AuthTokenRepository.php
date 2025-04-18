<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Repositories;

use DateInterval;
use Illuminate\Support\Facades\Cache;
use Mage\Framework\Auth\Contracts\UserEntityInterface;
use Mage\Framework\Auth\Exceptions\AuthException;
use Psr\Http\Message\ServerRequestInterface;
use Safe\DateTimeImmutable;

abstract class AuthTokenRepository
{
    private const int MAX_BYTES = 32;

    protected function token(
        ServerRequestInterface $request,
        UserEntityInterface $user,
        DateInterval $ttl,
        string $key
    ): array {
        $token = $this->getRandomString();
        $this->cachePut($key . $token, [
            'request' => serialize($request),
            'user' => serialize($user),
        ], $ttl);
        return [
            'expires_in' => $this->getExpiresIn($ttl),
            'token' => $token,
        ];
    }

    protected function validateToken(ServerRequestInterface $request): string
    {
        /** @psalm-var array{token: string} $parsedRequest */
        $parsedRequest = $request->getParsedBody();
        if (is_null($parsedRequest['token'] ?? null)) {
            throw AuthException::invalidToken();
        }
        return $parsedRequest['token'];
    }

    /**
     * @psalm-return array{0: ServerRequestInterface, 1: User}
     */
    protected function originalRequestData(string $key): array
    {
        /** @psalm-var array{request: string, user: string}|null $data */
        $data = $this->cacheGet($key);
        if (is_null($data)) {
            throw AuthException::invalidToken();
        }
        return $this->unserialize($data);
    }

    /**
     * @psalm-param array{request: string, user: string} $data
     * @psalm-return array{0: ServerRequestInterface, 1: User}
     */
    private function unserialize(array $data): array
    {
        /** @psalm-var ServerRequestInterface $request */
        $request = unserialize($data['request']);
        /** @psalm-var User $user */
        $user = unserialize($data['user']);
        return [$request, $user];
    }

    private function getRandomString(): string
    {
        return bin2hex(random_bytes(self::MAX_BYTES));
    }

    private function getExpiresIn(DateInterval $ttl): int
    {
        $now = (new DateTimeImmutable());
        return $now->add($ttl)->getTimestamp() - $now->getTimestamp();
    }

    protected function cacheGet(string $key): mixed
    {
        if (!Cache::has($key)) {
            return null;
        }
        /** @psalm-var string $value */
        $value = Cache::get($key);
        return decrypt($value);
    }

    /**
     * @param string|string[] $value
     *
     * @psalm-param array{request: string, user: string}|string $value
     */
    protected function cachePut(string $key, array|string $value, DateInterval $ttl): void
    {
        Cache::put($key, encrypt($value), $ttl);
    }

    protected function cacheForget(string $key): void
    {
        Cache::forget($key);
    }
}
