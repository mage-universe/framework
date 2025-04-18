<?php declare(strict_types=1);

namespace Tests\Framework\Auth;

use DateInterval;
use Mage\Framework\Auth\Grants\PasswordAuthGrant;
use Mage\Framework\Auth\PasswordAuth;
use Psr\Http\Message\ServerRequestInterface;
use Tests\Framework\Auth\Data\Factories\ClientFactory;
use Tests\Framework\Auth\Data\Factories\UserFactory;

class PasswordAuthTest extends AuthTestCase
{
    public function test_password_auth_filled_correctly(): void
    {
        $mock = $this->spy(PasswordAuthGrant::class);
        $mock->shouldReceive('setAccessTokenTTL')
            ->once()
            ->with($this->equalTo(DateInterval::createFromDateString('1 year')))
            ->andReturnSelf();
        $mock->shouldReceive('setAccessTokenTTL')
            ->once()
            ->with($this->equalTo(DateInterval::createFromDateString('2 years')))
            ->andReturnSelf();

        $mock->shouldReceive('setRefreshTokenTTL')
            ->once()
            ->with($this->equalTo(DateInterval::createFromDateString('2 years')))
            ->andReturnSelf();
        $mock->shouldReceive('setRefreshTokenTTL')
            ->once()
            ->with($this->equalTo(DateInterval::createFromDateString('4 years')))
            ->andReturnSelf();

        $mock->shouldReceive('setSingleLogin')->once()->with(false)->andReturnSelf();
        $mock->shouldReceive('setSingleLogin')->once()->with(true)->andReturnSelf();

        $mock->shouldReceive('setLockable')->once()->with(true)->andReturnSelf();
        $mock->shouldReceive('setLockable')->once()->with(false)->andReturnSelf();

        $mock->shouldReceive('setLockTTL')
            ->once()
            ->with($this->equalTo(DateInterval::createFromDateString('5 minutes')))
            ->andReturnSelf();
        $mock->shouldReceive('setLockTTL')
            ->once()
            ->with($this->equalTo(DateInterval::createFromDateString('10 minutes')))
            ->andReturnSelf();

        $mock->shouldReceive('setMaxTries')->once()->with(5)->andReturnSelf();
        $mock->shouldReceive('setMaxTries')->once()->with(3)->andReturnSelf();

        $mock->shouldReceive('setTriesTTL')
            ->once()
            ->with($this->equalTo(DateInterval::createFromDateString('30 seconds')))
            ->andReturnSelf();
        $mock->shouldReceive('setTriesTTL')
            ->once()
            ->with($this->equalTo(DateInterval::createFromDateString('60 seconds')))
            ->andReturnSelf();

        /** @psalm-var ServerRequestInterface $request */
        $request = $this->app?->make(ServerRequestInterface::class);
        /** @psalm-var PasswordAuthGrant $mock */
        $auth = new PasswordAuth($request, $mock);
        $auth->setAccessTokenTTL('2 years');
        $auth->setRefreshTokenTTL('4 years');
        $auth->setSingleLogin(true);
        $auth->setLockable(false);
        $auth->setLockTTL('10 minutes');
        $auth->setMaxTries(3);
        $auth->setTriesTTL('60 seconds');
    }

    public function test_password_auth_tfa_disabled_ok(): void
    {
        $client = ClientFactory::new()->create();
        $user = UserFactory::new()->create(['two_factor_app_code_enabled' => true]);

        /** @psalm-var PasswordAuth $auth */
        $auth = $this->app?->make(PasswordAuth::class);
        $response = $auth->respondToLoginRequest([
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'username' => $user->username,
            'password' => 'password',
        ]);
        $this->assertArrayHasKey('token_type', (array) json_decode((string) $response->getContent(), true));
    }

    public function test_password_auth_tfa_enabled_ok(): void
    {
        $client = ClientFactory::new()->create();
        $user = UserFactory::new()->create(['two_factor_app_code_enabled' => true]);

        /** @psalm-var PasswordAuth $auth */
        $auth = $this->app?->make(PasswordAuth::class);
        $auth->setTfaEnabled(true);

        $response = $auth->respondToLoginRequest([
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'username' => $user->username,
            'password' => 'password',
        ]);
        $this->assertArrayNotHasKey('token_type', (array) json_decode((string) $response->getContent(), true));
    }

    public function test_password_auth_tfa_enabled_no_user_tfa_ok(): void
    {
        $client = ClientFactory::new()->create();
        $user = UserFactory::new()->create();

        /** @psalm-var PasswordAuth $auth */
        $auth = $this->app?->make(PasswordAuth::class);
        $auth->setTfaEnabled(true);

        $response = $auth->respondToLoginRequest([
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'username' => $user->username,
            'password' => 'password',
        ]);
        $this->assertArrayHasKey('token_type', (array) json_decode((string) $response->getContent(), true));
    }
}
