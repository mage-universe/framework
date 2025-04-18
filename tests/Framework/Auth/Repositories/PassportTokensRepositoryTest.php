<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Repositories;

use Mage\Framework\Auth\Repositories\PassportTokensRepository;
use Tests\Framework\Auth\AuthTestCase;
use Tests\Framework\Auth\Data\Factories\AccessTokenFactory;
use Tests\Framework\Auth\Data\Factories\ClientFactory;
use Tests\Framework\Auth\Data\Factories\RefreshTokenFactory;

class PassportTokensRepositoryTest extends AuthTestCase
{
    public function test_delete_all_user_tokens_except_latest(): void
    {
        $clientId = $this->createAccessToken(3);
        $this->assertDatabaseCount('oauth_access_tokens', 3);
        $this->assertDatabaseCount('oauth_refresh_tokens', 3);
        $repository = new PassportTokensRepository();
        $repository->removeTokens(1, $clientId);
        $this->assertDatabaseCount('oauth_access_tokens', 0);
        $this->assertDatabaseCount('oauth_refresh_tokens', 0);
    }

    private function createAccessToken(int $times): string
    {
        $client = ClientFactory::new()->create();
        for ($i = 0; $i < $times; $i++) {
            $accessToken = AccessTokenFactory::new([
                'client_id' => $client->id,
            ])->create();
            RefreshTokenFactory::new([
                'access_token_id' => $accessToken->id,
            ])->create();
        }

        return (string) $client->id;
    }
}
