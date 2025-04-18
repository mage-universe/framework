<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use Mage\Framework\Auth\Mail\TwoFactorEmail;
use Mage\Framework\Auth\PasswordAuth;
use Tests\Framework\Auth\AuthTestCase;
use Tests\Framework\Auth\Data\Controllers\NonDefaultPasswordAuth2Controller;
use Tests\Framework\Auth\Data\Controllers\NonDefaultPasswordAuthController;
use Tests\Framework\Auth\Data\Controllers\PasswordAuthController;
use Tests\Framework\Auth\Data\Factories\ClientFactory;
use Tests\Framework\Auth\Data\Factories\UserFactory;
use Tests\Framework\Auth\Data\Mail\TwoFactorEmailVariation;

class PasswordAuthControllerTest extends AuthTestCase
{
    protected function defineRoutes($router): void
    {
        $router->post('auth/login', [PasswordAuthController::class, 'login'])
            ->name('auth.password.login');
        $router->post('auth/login2', [NonDefaultPasswordAuthController::class, 'login'])
            ->name('auth.password.no-tfa-login');
        $router->post('auth/email', [PasswordAuthController::class, 'email'])
            ->name('auth.password.email');
        $router->post('auth/email2', [NonDefaultPasswordAuth2Controller::class, 'email'])
            ->name('auth.password.email-variation');
        $router->post('auth/tfa', [PasswordAuthController::class, 'tfa'])
            ->name('auth.password.tfa');
    }

    public function test_build_controller_ok(): void
    {
        $mock = $this->mock(PasswordAuth::class);
        $mock->shouldReceive('setAccessTokenTTL')->once()->with('1 year')->andReturnSelf();
        $mock->shouldReceive('setRefreshTokenTTL')->once()->with('2 years')->andReturnSelf();
        $mock->shouldReceive('setExtraData')->once()->with([])->andReturnSelf();
        $mock->shouldReceive('setResponseAsCookie')->once()->with(false)->andReturnSelf();
        $mock->shouldReceive('setSingleLogin')->once()->with(false)->andReturnSelf();
        $mock->shouldReceive('setTfaEnabled')->once()->with(true)->andReturnSelf();
        $mock->shouldReceive('setTfaTTL')->once()->with('5 minutes')->andReturnSelf();
        $mock->shouldReceive('setLockable')->once()->with(true)->andReturnSelf();
        $mock->shouldReceive('setLockTTL')->once()->with('5 minutes')->andReturnSelf();
        $mock->shouldReceive('setMaxTries')->once()->with(5)->andReturnSelf();
        $mock->shouldReceive('setTriesTTL')->once()->with('30 seconds')->andReturnSelf();
        $mock->shouldReceive('setTfaMail')
            ->once()
            ->with($this->equalTo(fn (string $code) => new TwoFactorEmail($code)))
            ->andReturnSelf();

        /** @psalm-var PasswordAuth $mock */
        new PasswordAuthController($mock);
    }

    public function test_build_controller_with_non_defaults_ok(): void
    {
        $mock = $this->mock(PasswordAuth::class);
        $mock->shouldReceive('setAccessTokenTTL')->once()->with('1 year')->andReturnSelf();
        $mock->shouldReceive('setRefreshTokenTTL')->once()->with('2 years')->andReturnSelf();
        $mock->shouldReceive('setExtraData')->once()->with([])->andReturnSelf();
        $mock->shouldReceive('setResponseAsCookie')->once()->with(false)->andReturnSelf();
        $mock->shouldReceive('setSingleLogin')->once()->with(false)->andReturnSelf();
        $mock->shouldReceive('setTfaEnabled')->once()->with(false)->andReturnSelf();
        $mock->shouldReceive('setTfaTTL')->once()->with('30 minutes')->andReturnSelf();
        $mock->shouldReceive('setLockable')->once()->with(false)->andReturnSelf();
        $mock->shouldReceive('setLockTTL')->once()->with('10 minutes')->andReturnSelf();
        $mock->shouldReceive('setMaxTries')->once()->with(2)->andReturnSelf();
        $mock->shouldReceive('setTriesTTL')->once()->with('15 seconds')->andReturnSelf();
        $mock->shouldReceive('setTfaMail')
            ->once()
            ->with($this->equalTo(fn (string $code) => new TwoFactorEmail($code)))
            ->andReturnSelf();

        /** @psalm-var PasswordAuth $mock */
        new NonDefaultPasswordAuthController($mock);
    }

    public function test_email_tfa_login_ok(): void
    {
        Mail::fake();
        $user = UserFactory::new()->create([
            'two_factor_email_enabled' => true,
            'two_factor_app_code_enabled' => true,
        ]);
        ClientFactory::new()->create();

        $this->postJson(route('auth.password.login'), [
            'username' => $user->username,
            'password' => 'password',
        ])->assertJson([
            'expires_in' => 300,
            'token' => 'token',
            'types' => ['email', 'code'],
        ])->assertStatus(200);

        $this->postJson(route('auth.password.email'), [
            'token' => 'token',
        ])->assertStatus(204);

        $code = '';
        Mail::assertQueued(TwoFactorEmail::class, function (TwoFactorEmail $mail) use (&$code) {
            $code = $mail->code;
            return true;
        });

        $response = $this->postJson(route('auth.password.tfa'), [
            'token' => 'token',
            'code' => $code,
            'type' => 'email',
        ]);
        $response->assertStatus(200);
        $this->assertArrayNotHasKey('extra', (array) json_decode((string) $response->getContent(), true));
    }

    public function test_email_tfa_login_with_mail_variant_ok(): void
    {
        Mail::fake();
        $user = UserFactory::new()->create([
            'two_factor_email_enabled' => true,
            'two_factor_app_code_enabled' => true,
        ]);
        ClientFactory::new()->create();

        $this->postJson(route('auth.password.login'), [
            'username' => $user->username,
            'password' => 'password',
        ])->assertJson([
            'expires_in' => 300,
            'token' => 'token',
            'types' => ['email', 'code'],
        ])->assertStatus(200);

        $this->postJson(route('auth.password.email-variation'), [
            'token' => 'token',
        ])->assertStatus(204);

        $code = '';
        Mail::assertQueued(TwoFactorEmailVariation::class, function (TwoFactorEmailVariation $mail) use (&$code) {
            $code = $mail->code;
            return true;
        });

        $response = $this->postJson(route('auth.password.tfa'), [
            'token' => 'token',
            'code' => $code,
            'type' => 'email',
        ]);
        $response->assertStatus(200);
        $this->assertArrayNotHasKey('extra', (array) json_decode((string) $response->getContent(), true));
    }

    public function test_login_no_tfa_ok(): void
    {
        $user = UserFactory::new()->create();
        ClientFactory::new()->create();

        $this->postJson(route('auth.password.no-tfa-login'), [
            'username' => $user->username,
            'password' => 'password',
        ])->assertJsonStructure(['token_type', 'expires_in', 'access_token', 'refresh_token', ])->assertStatus(200);
    }

    public function test_email_tfa_login_invalid_token_request(): void
    {
        $response = $this->postJson(route('auth.password.tfa'), [
            'token' => 'tokenn',
            'code' => '12345',
            'type' => 'email',
        ]);
        $response->assertStatus(401);
        $this->assertEquals([
            'status' => 'Unauthorized',
            'message' => 'auth.invalid_token',
            'data' => [],
        ], (array) json_decode((string) $response->getContent(), true));
    }

    public function test_email_tfa_login_invalid_code_request(): void
    {
        $user = UserFactory::new()->create();
        ClientFactory::new()->create();

        $this->postJson(route('auth.password.login'), [
            'username' => $user->username,
            'password' => 'password',
        ])->assertStatus(200);

        $response = $this->postJson(route('auth.password.tfa'), [
            'token' => 'token',
            'code' => '12345',
            'type' => 'email',
        ]);
        $response->assertStatus(403);
        $this->assertEquals([
            'status' => 'Forbidden',
            'message' => 'auth.invalid_two_factor_code',
            'data' => [],
        ], (array) json_decode((string) $response->getContent(), true));
    }
}
