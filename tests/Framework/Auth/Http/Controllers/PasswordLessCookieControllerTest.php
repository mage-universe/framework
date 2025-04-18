<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use Mage\Framework\Auth\Mail\MagicLinkMail;
use Mage\Framework\Auth\PasswordLessAuth;
use Tests\Framework\Auth\AuthTestCase;
use Tests\Framework\Auth\Data\Controllers\MagicLinkCookieController;
use Tests\Framework\Auth\Data\Factories\ClientFactory;
use Tests\Framework\Auth\Data\Factories\UserFactory;

class PasswordLessCookieControllerTest extends AuthTestCase
{
    protected function defineRoutes($router): void
    {
        $router->post('auth/magic-link', [MagicLinkCookieController::class, 'login'])
            ->name('auth.magic-link.request');
        $router->post('auth/magic-link/validate', [MagicLinkCookieController::class, 'validate'])
            ->name('auth.magic-link.validate');
    }

    public function test_build_controller_ok(): void
    {
        $mock = $this->mock(PasswordLessAuth::class);
        $mock->shouldReceive('setAccessTokenTTL')->once()->with('1 year')->andReturnSelf();
        $mock->shouldReceive('setRefreshTokenTTL')->once()->with('2 years')->andReturnSelf();
        $mock->shouldReceive('setExtraData')->once()->with(['extra' => 'data'])->andReturnSelf();
        $mock->shouldReceive('setResponseAsCookie')->once()->with(true)->andReturnSelf();
        $mock->shouldReceive('setSingleLogin')->once()->with(false)->andReturnSelf();

        $mock->shouldReceive('setLinkUrl')->once()->with(route('auth.magic-link.validate'))->andReturnSelf();
        $mock->shouldReceive('setValidateRouteName')->once()->with('auth.magic-link.validate')->andReturnSelf();
        $mock->shouldReceive('setLinkTTL')->once()->with('5 minutes')->andReturnSelf();
        $mock->shouldReceive('setLinkMail')
            ->once()
            ->with($this->equalTo(fn (string $link) => new MagicLinkMail($link)))
            ->andReturnSelf();

        /** @psalm-var PasswordLessAuth $mock */
        new MagicLinkCookieController($mock);
    }

    public function test_magic_link_login_ok(): void
    {
        Mail::fake();
        $user = UserFactory::new()->create();
        ClientFactory::new()->create();

        $link = '';
        $this->postJson(route('auth.magic-link.request'), [
            'username' => $user->username,
        ])->assertStatus(204);
        Mail::assertQueued(MagicLinkMail::class, function (MagicLinkMail $mail) use (&$link) {
            $link = $mail->link;
            return true;
        });

        $response = $this->postJson($link, []);
        $response->assertCookie('token')->assertStatus(204);
    }

    public function test_magic_link_login_invalid_request(): void
    {
        $response = $this->postJson(route('auth.magic-link.validate'), []);
        $response->assertJson([
            'status' => 'Unauthorized',
            'message' => 'validation.invalid-signature',
            'data' => [],
        ])->assertStatus(401);
    }
}
