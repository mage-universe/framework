<?php declare(strict_types=1);

namespace Tests\Framework\Auth;

use DateInterval;
use Illuminate\Support\Facades\Mail;
use Mage\Framework\Auth\Grants\PasswordLessGrant;
use Mage\Framework\Auth\Mail\MagicLinkMail;
use Mage\Framework\Auth\PasswordLessAuth;
use Psr\Http\Message\ServerRequestInterface;
use Tests\Framework\Auth\Data\Controllers\MagicLinkController;
use Tests\Framework\Auth\Data\Factories\ClientFactory;
use Tests\Framework\Auth\Data\Factories\UserFactory;

class PasswordLessAuthTest extends AuthTestCase
{
    protected function defineRoutes($router): void
    {
        $router->post('auth/magic-link', [MagicLinkController::class, '_login'])
            ->name('auth.magic-link.request');
        $router->post('auth/magic-link/validate', [MagicLinkController::class, '_validate'])
            ->name('auth.magic-link.validate');
    }

    public function test_password_less_auth_filled_correctly(): void
    {
        $mock = $this->spy(PasswordLessGrant::class);
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

        /** @psalm-var ServerRequestInterface $request */
        $request = $this->app?->make(ServerRequestInterface::class);
        /** @psalm-var PasswordLessGrant $mock */
        $auth = new PasswordLessAuth($request, $mock);
        $auth->setAccessTokenTTL('2 years');
        $auth->setRefreshTokenTTL('4 years');
        $auth->setSingleLogin(true);
    }

    public function test_password_auth_tfa_enabled_ok(): void
    {
        Mail::fake();
        $user = UserFactory::new()->create();
        $client = ClientFactory::new()->create();

        /** @psalm-var PasswordLessAuth $auth */
        $auth = $this->app?->make(PasswordLessAuth::class);
        $auth->setLinkUrl('http://localhost');
        $auth->setValidateRouteName('auth.magic-link.validate');
        $auth->setResponseAsCookie(true);
        $auth->setLinkMail(fn (string $link) => new MagicLinkMail($link));

        $auth->respondToLoginRequest([
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'username' => $user->email,
        ]);
        Mail::assertQueued(MagicLinkMail::class);

        $response = $auth->respondToMagicLinkTokenRequest([
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'token' => 'token',
        ]);
        $this->assertEquals(204, $response->getStatusCode());
    }
}
