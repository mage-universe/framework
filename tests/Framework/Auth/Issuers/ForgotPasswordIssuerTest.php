<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Issuers;

use DateInterval;
use Illuminate\Support\Facades\Mail;
use Mage\Framework\Auth\Issuers\ForgotPasswordIssuer;
use Mage\Framework\Auth\Mail\ResetPasswordMail;
use Tests\Framework\Auth\AuthTestCase;
use Tests\Framework\Auth\Data\Controllers\ResetPasswordController;
use Tests\Framework\Auth\Data\Factories\ClientFactory;
use Tests\Framework\Auth\Data\Factories\UserFactory;

class ForgotPasswordIssuerTest extends AuthTestCase
{
    protected function defineRoutes($router): void
    {
        $router->post('auth/forgot', [ResetPasswordController::class, 'forgot'])
            ->name('auth.reset-password.forgot');
        $router->post('auth/reset', [ResetPasswordController::class, '_reset'])
            ->name('auth.reset-password.reset');
    }

    public function test_forgot_password_issuer_response_ok(): void
    {
        Mail::fake();
        ClientFactory::new()->create();
        /** @psalm-var object{ username:string|null, password:string|null } $user */
        $user = UserFactory::new()->create();

        $request = $this->getRequest($user->username, 'password');
        $response = (new ForgotPasswordIssuer($request, $this->getPasswordAuthGrant()))
            ->email(
                'localhost:3000',
                'auth.reset-password.reset',
                new DateInterval('P1D'),
                fn (string $link) => new ResetPasswordMail($link)
            );

        $this->assertEquals(204, $response->getStatusCode());
        Mail::assertQueued(ResetPasswordMail::class);
    }
}
