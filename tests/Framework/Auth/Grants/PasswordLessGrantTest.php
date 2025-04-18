<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Grants;

use Illuminate\Support\Facades\Mail;
use Mage\Framework\Auth\Exceptions\AuthException;
use Mage\Framework\Auth\Mail\MagicLinkMail;
use Tests\Framework\Auth\AuthTestCase;
use Tests\Framework\Auth\Data\Controllers\MagicLinkController;
use Tests\Framework\Auth\Data\Factories\ClientFactory;
use Tests\Framework\Auth\Data\Factories\UserFactory;

class PasswordLessGrantTest extends AuthTestCase
{
    protected function defineRoutes($router): void
    {
        $router->post('auth/magic-link', [MagicLinkController::class, 'login'])
            ->name('auth.magic-link.request');
    }

    public function test_password_less_grant_token_request_mailed_ok(): void
    {
        Mail::fake();

        ClientFactory::new()->create();
        /** @psalm-var object{ username:string|null, password:string|null } $user */
        $user = UserFactory::new()->create();
        $request = $this->getRequest($user->username, 'password');

        $this->getPasswordLessGrant()->respondToMagicLinkTokenRequest(
            $request,
            'localhost:3000',
            'auth.magic-link.request',
            $this->getTtl(),
            fn (string $link) => new MagicLinkMail($link)
        );

        Mail::assertQueued(MagicLinkMail::class);
    }

    public function test_null_username_exception(): void
    {
        ClientFactory::new()->create();

        $this->expectExceptionMessage(AuthException::invalidCredentials()->getMessage());

        $request = $this->getRequest(null, null, '');
        $this->getPasswordLessGrant()->respondToMagicLinkTokenRequest(
            $request,
            'localhost:3000',
            'auth.magic-link.request',
            $this->getTtl(),
            fn (string $link) => new MagicLinkMail($link)
        );
    }
}
