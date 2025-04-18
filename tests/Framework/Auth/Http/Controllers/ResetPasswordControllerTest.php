<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Validator;
use Mage\Framework\Auth\Mail\ResetPasswordMail;
use Mage\Framework\Auth\ResetPasswordAuth;
use Tests\Framework\Auth\AuthTestCase;
use Tests\Framework\Auth\Data\Controllers\NonDefaultPasswordAuthController;
use Tests\Framework\Auth\Data\Controllers\NonDefaultResetPasswordController;
use Tests\Framework\Auth\Data\Controllers\ResetPasswordController;
use Tests\Framework\Auth\Data\Factories\ClientFactory;
use Tests\Framework\Auth\Data\Factories\UserFactory;
use Tests\Framework\Auth\Data\Mail\ResetPasswordMailVariation;

class ResetPasswordControllerTest extends AuthTestCase
{
    protected function defineRoutes($router): void
    {
        $router->post('auth/forgot', [ResetPasswordController::class, 'forgot'])
            ->name('auth.reset-password.forgot');
        $router->post('auth/forgot2', [NonDefaultResetPasswordController::class, 'forgot'])
            ->name('auth.reset-password.forgot2');
        $router->post('auth/reset', [ResetPasswordController::class, 'reset'])
            ->name('auth.reset-password.reset');
        $router->post('auth/reset2', [NonDefaultResetPasswordController::class, 'reset'])
            ->name('auth.reset-password.reset2');
        $router->post('auth/login2', [NonDefaultPasswordAuthController::class, 'login'])
            ->name('auth.password.no-tfa-login');
    }

    public function test_build_controller_ok(): void
    {
        $mock = $this->mock(ResetPasswordAuth::class);

        $mock->shouldReceive('setLinkUrl')->once()->with(route('auth.reset-password.reset'))->andReturnSelf();
        $mock->shouldReceive('setResetRouteName')->once()->with('auth.reset-password.reset')->andReturnSelf();
        $mock->shouldReceive('setLinkTTL')->once()->with('60 minutes')->andReturnSelf();
        $mock->shouldReceive('setLinkMail')
            ->once()
            ->with($this->equalTo(fn (string $link) => new ResetPasswordMail($link)))
            ->andReturnSelf();

        /** @psalm-var ResetPasswordAuth $mock */
        new ResetPasswordController($mock);
    }

    public function test_build_non_default_controller_ok(): void
    {
        $mock = $this->mock(ResetPasswordAuth::class);

        $mock->shouldReceive('setLinkUrl')->once()->with('http://localhost/auth/reset')->andReturnSelf();
        $mock->shouldReceive('setResetRouteName')->once()->with('auth.reset-password.reset2')->andReturnSelf();
        $mock->shouldReceive('setLinkTTL')->once()->with('2 hours')->andReturnSelf();
        $mock->shouldReceive('setLinkMail')
            ->once()
            ->with($this->equalTo(fn (string $link) => new ResetPasswordMailVariation($link)))
            ->andReturnSelf();

        /** @psalm-var ResetPasswordAuth $mock */
        new NonDefaultResetPasswordController($mock);
    }

    public function test_forgot_password_email_ok(): void
    {
        Mail::fake();
        $user = UserFactory::new()->create();
        ClientFactory::new()->create();

        $this->postJson(route('auth.reset-password.forgot'), [
            'username' => $user->username,
        ])->assertStatus(204);

        Mail::assertQueued(ResetPasswordMail::class);
    }

    public function test_forgot_password_email_variant_ok(): void
    {
        Mail::fake();
        $user = UserFactory::new()->create();
        ClientFactory::new()->create();

        $this->postJson(route('auth.reset-password.forgot2'), [
            'username' => $user->username,
        ])->assertStatus(204);

        Mail::assertQueued(ResetPasswordMailVariation::class);
    }

    public function test_reset_password_email_ok(): void
    {
        Mail::fake();
        $user = UserFactory::new()->create();
        ClientFactory::new()->create();

        $this->postJson(route('auth.reset-password.forgot'), [
            'username' => $user->username,
        ])->assertStatus(204);

        $link = '';
        Mail::assertQueued(ResetPasswordMail::class, function (ResetPasswordMail $mail) use (&$link) {
            $link = $mail->link;
            return true;
        });

        $this->postJson($link, [
            'username' => $user->username,
            'password' => 'Pa$word12345',
            'password_confirmation' => 'Pa$word12345',
        ])->assertStatus(204);
    }

    public function test_reset_password_logout_all_sessions(): void
    {
        $user = UserFactory::new()->create();
        ClientFactory::new()->create();

        $this->postJson(route('auth.password.no-tfa-login'), [
            'username' => $user->username,
            'password' => 'password',
        ])->assertJsonStructure(['token_type', 'expires_in', 'access_token', 'refresh_token', ])->assertStatus(200);

        $this->assertDatabaseHas('oauth_access_tokens', [
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseCount('oauth_access_tokens', 1);

        Mail::fake();
        $this->postJson(route('auth.reset-password.forgot'), [
            'username' => $user->username,
        ])->assertStatus(204);

        $link = '';
        Mail::assertQueued(ResetPasswordMail::class, function (ResetPasswordMail $mail) use (&$link) {
            $link = $mail->link;
            return true;
        });
        $this->assertDatabaseCount('password_reset_tokens', 1);
        $this->assertDatabaseCount('oauth_access_tokens', 1);

        $this->postJson($link, [
            'username' => $user->username,
            'password' => 'Pa$sword12345',
            'password_confirmation' => 'Pa$sword12345',
        ])->assertStatus(204);

        $this->assertDatabaseMissing('oauth_access_tokens', [
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseCount('oauth_access_tokens', 0);
        $this->assertDatabaseCount('oauth_refresh_tokens', 0);

        $this->assertDatabaseMissing('password_reset_tokens', [
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_reset_password_invalid_request(): void
    {
        $this->postJson(route('auth.password.no-tfa-login'), [])
            ->assertJson([
                'status' => 'Unprocessable Content',
                'message' => 'validation.summary',
                'data' => [
                    'username' => ['The username field is required.'],
                    'password' => ['The password field is required.'],
                ],
            ])->assertStatus(422);

        $this->postJson(route('auth.reset-password.forgot'), [])
            ->assertJson([
                'status' => 'Unprocessable Content',
                'message' => 'validation.summary',
                'data' => [
                    'username' => ['The username field is required.'],
                ],
            ])
            ->assertStatus(422);

        $user = UserFactory::new()->create();
        ClientFactory::new()->create();

        Mail::fake();
        $this->postJson(route('auth.reset-password.forgot'), [
            'username' => $user->username,
        ])->assertStatus(204);

        $link = '';
        Mail::assertQueued(ResetPasswordMail::class, function (ResetPasswordMail $mail) use (&$link) {
            $link = $mail->link;
            return true;
        });

        $this->postJson($link, [])
            ->assertJson([
                'status' => 'Unprocessable Content',
                'message' => 'validation.summary',
                'data' => [
                    'username' => ['The username field is required.'],
                    'password' => ['The password field is required.'],
                ],
            ])
            ->assertStatus(422);

        $this->postJson(route('auth.reset-password.reset'), [])
            ->assertJson([
                'status' => 'Unauthorized',
                'message' => 'validation.invalid-signature',
                'data' => [],
            ])
            ->assertStatus(401);

        $this->postJson($link, [
            'password' => 1,
        ])
            ->assertJson([
                'status' => 'Unprocessable Content',
                'message' => 'validation.summary',
                'data' => [
                    'username' => ['The username field is required.'],
                    'password' => [
                        'The password field must be a string.',
                        'The password field confirmation does not match.',
                        'The password field must be at least 12 characters.',
                        'validation.letters_and_numbers',
                        'validation.uppercase_and_lowercase',
                    ],
                ],
            ])
            ->assertStatus(422);
    }

    public function test_reset_password_on_non_default_invalid_request(): void
    {
        $this->postJson(route('auth.reset-password.reset2'), [])
            ->assertJson([
                'status' => 'Unprocessable Content',
                'message' => 'validation.summary',
                'data' => [
                    'username' => ['prefix.required'],
                    'token' => ['prefix.required'],
                    'password' => ['prefix.required'],
                ],
            ])
            ->assertStatus(422);

        $response = $this->postJson(route('auth.reset-password.reset2'), [
            'password' => 1,
        ])
            ->assertJson([
                'status' => 'Unprocessable Content',
                'message' => 'validation.summary',
                'data' => [
                    'username' => ['prefix.required'],
                    'token' => ['prefix.required'],
                    'password' => [
                        'prefix.string',
                        'prefix.confirmed',
                        'prefix.min.string',
                        'prefix.letters_and_numbers',
                        'prefix.uppercase_and_lowercase',
                        'prefix.special_characters',
                    ],
                ],
            ])
            ->assertStatus(422);

        /** @psalm-var object{validator: Validator} $exception */
        $exception = $response->baseResponse->exception;

        $this->assertEquals([
            'username.required' => 'prefix.required',
            'username.string' => 'prefix.string',
            'token.required' => 'prefix.required',
            'token.string' => 'prefix.string',
            'password.required' => 'prefix.required',
            'password.string' => 'prefix.string',
            'password.confirmed' => 'prefix.confirmed',
        ], $exception->validator->customMessages);
    }
}
