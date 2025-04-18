<?php declare(strict_types=1);

namespace Tests\Framework\Auth;

use Mage\Framework\Auth\Grants\PasswordAuthGrant;
use Mage\Framework\Auth\ResetPasswordAuth;
use Psr\Http\Message\ServerRequestInterface;
use Tests\Framework\Auth\Data\Controllers\ResetPasswordController;
use Tests\Framework\Auth\Data\Factories\ClientFactory;
use Tests\Framework\Auth\Data\Factories\UserFactory;

class ResetPasswordAuthTest extends AuthTestCase
{
    protected function defineRoutes($router): void
    {
        $router->post('auth/forgot', [ResetPasswordController::class, 'forgot'])
            ->name('auth.reset-password.forgot');
        $router->post('auth/reset', [ResetPasswordController::class, '_reset'])
            ->name('auth.reset-password.reset');
    }

    public function test_reset_password_issuer_response_ok(): void
    {
        UserFactory::new()->create();
        ClientFactory::new()->create();

        $mock = $this->spy(PasswordAuthGrant::class);

        /** @psalm-var ServerRequestInterface $request */
        $request = $this->app?->make(ServerRequestInterface::class);
        /** @psalm-var PasswordAuthGrant $mock */
        $auth = new ResetPasswordAuth($request, $mock);
        $auth->setLinkMail(fn () => null);
        $auth->setLinkUrl('localhost');
        $auth->setResetRouteName('auth.reset-password.reset');
        $auth->setLinkTTL('30 minutes');

        $response = $auth->issueForgotPasswordTokenToEmail(['email' => 'test@test.com']);
        $this->assertEquals(204, $response->getStatusCode());
    }
}
