<?php declare(strict_types=1);

namespace Mage\Framework\Auth\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;
use Mage\Framework\Auth\Contracts\PassportTokensInterface;

final class PassportTokensRepository implements PassportTokensInterface
{
    public function removeTokens(int $userId, string $clientId): void
    {
        /** @psalm-var Builder $client */
        $client = Passport::client();

        /** @psalm-var Client $authClient */
        $authClient = $client->where('id', $clientId)->first();

        /** @psalm-var Builder $builder */
        $builder = $authClient->tokens()->where('user_id', $userId);
        $tokens = $builder->pluck('id')->toArray();

        /** @psalm-var Builder $tokenModel */
        $tokenModel = Passport::token();
        $tokenModel->whereIn('id', $tokens)->delete();

        /** @psalm-var Builder $refreshToken */
        $refreshToken = Passport::refreshToken();
        $refreshToken->whereIn('access_token_id', $tokens)->delete();
    }
}
