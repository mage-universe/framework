<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Http\Controllers;

use Tests\Framework\Auth\AuthTestCase;
use Tests\Framework\Auth\Data\Controllers\PasswordAuthCookieController;
use Tests\Framework\Auth\Data\Factories\ClientFactory;
use Tests\Framework\Auth\Data\Factories\UserFactory;

class PasswordAuthCookieControllerTest extends AuthTestCase
{
    protected function defineRoutes($router): void
    {
        $router->post('auth/login', [PasswordAuthCookieController::class, 'login'])
            ->name('auth.password.login');
    }

    public function test_login_returning_cookie_ok(): void
    {
        $user = UserFactory::new()->create();
        ClientFactory::new()->create();

        $this->postJson(route('auth.password.login'), [
            'username' => $user->username,
            'password' => 'password',
        ])->assertCookie('token')->assertStatus(204);
    }
}
