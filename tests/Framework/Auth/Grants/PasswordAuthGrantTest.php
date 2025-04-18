<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Grants;

use DateInterval;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Mage\Framework\Auth\Exceptions\AuthException;
use Mage\Framework\Auth\Grants\PasswordAuthGrant;
use Mage\Framework\Auth\Mail\ResetPasswordMail;
use RobThree\Auth\TwoFactorAuth;
use Tests\Framework\Auth\AuthTestCase;
use Tests\Framework\Auth\Data\Controllers\ResetPasswordController;
use Tests\Framework\Auth\Data\Factories\ClientFactory;
use Tests\Framework\Auth\Data\Factories\UserFactory;

class PasswordAuthGrantTest extends AuthTestCase
{
    protected function defineRoutes($router): void
    {
        $router->post('auth/forgot', [ResetPasswordController::class, 'forgot'])
            ->name('auth.reset-password.forgot');
        $router->post('auth/reset', [ResetPasswordController::class, 'reset'])
            ->name('auth.reset-password.reset');
    }

    public function test_auth_request_methods_ok(): void
    {
        ClientFactory::new()->create();
        /** @psalm-var object{ username:string|null, password:string|null } $user */
        $user = UserFactory::new()->create([
            'two_factor_email_enabled' => true,
            'two_factor_app_code_enabled' => true,
        ]);
        $request = $this->getRequest($user->username, 'password');

        $response = $this->getPasswordAuthGrant()->respondToTwoFactorRequest($request, $this->getTtl());
        $this->assertEquals([
            'expires_in' => 3600,
            'token' => 'token',
            'types' => ['email', 'code', ],
        ], $response);
    }

    public function test_auth_request_single_login_ok(): void
    {
        ClientFactory::new()->create();
        /** @psalm-var object{ username:string|null, password:string|null, two_factor_secret:string } $user */
        $user = UserFactory::new()->create();

        $request = $this->getRequest($user->username, 'password');
        /** @psalm-var PasswordAuthGrant $grant */
        $grant = $this->getPasswordAuthGrant()->setSingleLogin(true);
        $grant->respondToTwoFactorRequest($request, $this->getTtl());

        $code = (new TwoFactorAuth())->getCode($user->two_factor_secret);
        $request = $this->getRequest($user->username, 'password', 'token', $code, 'code');
        $grant->respondToTwoFactorValidatorRequest($request);

        $request = $this->getRequest($user->username, 'password');
        /** @psalm-var PasswordAuthGrant $grant */
        $grant = $this->getPasswordAuthGrant()->setSingleLogin(true);
        $grant->respondToTwoFactorRequest($request, $this->getTtl());

        $code = (new TwoFactorAuth())->getCode($user->two_factor_secret);
        $request = $this->getRequest($user->username, 'password', 'token', $code, 'code');
        $grant->respondToTwoFactorValidatorRequest($request);

        $this->assertDatabaseCount('oauth_access_tokens', 1);
    }

    public function test_auth_request_single_login_false_ok(): void
    {
        ClientFactory::new()->create();
        /** @psalm-var object{ username:string|null, password:string|null, two_factor_secret:string } $user */
        $user = UserFactory::new()->create();

        $request = $this->getRequest($user->username, 'password');
        /** @psalm-var PasswordAuthGrant $grant */
        $grant = $this->getPasswordAuthGrant()->setSingleLogin(false);
        $grant->respondToTwoFactorRequest($request, $this->getTtl());

        $code = (new TwoFactorAuth())->getCode($user->two_factor_secret);
        $request = $this->getRequest($user->username, 'password', 'token', $code, 'code');
        $grant->respondToTwoFactorValidatorRequest($request);

        $request = $this->getRequest($user->username, 'password');
        /** @psalm-var PasswordAuthGrant $grant */
        $grant = $this->getPasswordAuthGrant()->setSingleLogin(false);
        $grant->respondToTwoFactorRequest($request, $this->getTtl());

        $code = (new TwoFactorAuth())->getCode($user->two_factor_secret);
        $request = $this->getRequest($user->username, 'password', 'token', $code, 'code');
        $grant->respondToTwoFactorValidatorRequest($request);

        $this->assertDatabaseCount('oauth_access_tokens', 2);
    }

    public function test_invalid_auth_exception(): void
    {
        $this->expectExceptionMessage(AuthException::invalidCredentials()->getMessage());

        ClientFactory::new()->create();
        /** @psalm-var object{ username:string|null, password:string|null } $user */
        $request = $this->getRequest(null, null);
        $this->getPasswordAuthGrant()->respondToTwoFactorRequest($request, $this->getTtl());
    }

    public function test_invalid_auth_lock_increments_exception(): void
    {
        $this->expectExceptionMessage(AuthException::invalidCredentials()->getMessage());

        ClientFactory::new()->create();
        /** @psalm-var object{ username:string, password:string } $user */
        $user = UserFactory::new()->create();
        $request = $this->getRequest($user->username, 'passwordd');

        $this->getPasswordAuthGrant()
            ->setLockable(true)
            ->setLockTTL(DateInterval::createFromDateString('60 seconds'))
            ->setMaxTries(3)
            ->setTriesTTL(DateInterval::createFromDateString('10 minutes'))
            ->respondToTwoFactorRequest($request, $this->getTtl());

        $this->assertTrue(Cache::get('auth.lock.' . $user->username . '.tries') === 1);
    }

    public function test_invalid_auth_result_on_account_locked(): void
    {
        $this->expectExceptionMessage(AuthException::lockedAccount()->getMessage());

        ClientFactory::new()->create();
        /** @psalm-var object{ username:string, password:string } $user */
        $user = UserFactory::new()->create();
        $request = $this->getRequest($user->username, 'passwordd');

        Cache::put('auth.lock.' . $user->username, true, 60);

        $this->getPasswordAuthGrant()
            ->setLockable(true)
            ->setLockTTL(DateInterval::createFromDateString('60 seconds'))
            ->setMaxTries(3)
            ->setTriesTTL(DateInterval::createFromDateString('10 minutes'))
            ->respondToTwoFactorRequest($request, $this->getTtl());
    }

    public function test_forgot_password_email(): void
    {
        Mail::fake();

        ClientFactory::new()->create();
        /** @psalm-var object{ username:string|null, password:string|null } $user */
        $user = UserFactory::new()->create();
        $request = $this->getRequest($user->username, 'password');

        $this->getPasswordAuthGrant()->queueForgotPasswordEmail(
            $request,
            'localhost:3000',
            'auth.reset-password.reset',
            $this->getTtl(),
            fn (string $link) => new ResetPasswordMail($link)
        );

        Mail::assertQueued(ResetPasswordMail::class);
    }

    public function test_reset_password_on_invalid_token_exception(): void
    {
        $this->expectExceptionMessage('auth.invalid_token');

        ClientFactory::new()->create();
        /** @psalm-var object{ username:string|null, password:string|null } $user */
        $user = UserFactory::new()->create();
        $request = $this->getRequest($user->username, 'password');

        $this->getPasswordAuthGrant()->resetPassword($request);
    }
}
