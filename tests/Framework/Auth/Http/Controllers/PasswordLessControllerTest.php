<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use Mage\Framework\Auth\Mail\MagicLinkMail;
use Mage\Framework\Auth\PasswordLessAuth;
use Tests\Framework\Auth\AuthTestCase;
use Tests\Framework\Auth\Data\Controllers\MagicLinkController;
use Tests\Framework\Auth\Data\Controllers\NonDefaultMagicLinkController;
use Tests\Framework\Auth\Data\Factories\ClientFactory;
use Tests\Framework\Auth\Data\Factories\UserFactory;
use Tests\Framework\Auth\Data\Mail\MagicLinkMailVariation;

class PasswordLessControllerTest extends AuthTestCase
{
    protected function defineRoutes($router): void
    {
        $router->post('auth/magic-link', [MagicLinkController::class, 'login'])
            ->name('auth.magic-link.request');
        $router->post('auth/magic-link2', [NonDefaultMagicLinkController::class, 'login'])
            ->name('auth.magic-link.request2');
        $router->post('auth/magic-link/validate', [MagicLinkController::class, 'validate'])
            ->name('auth.magic-link.validate');
        $router->post('auth/magic-link/validate2', [NonDefaultMagicLinkController::class, 'validate'])
            ->name('auth.magic-link.validate2');
    }

    public function test_build_controller_ok(): void
    {
        $mock = $this->mock(PasswordLessAuth::class);
        $mock->shouldReceive('setAccessTokenTTL')->once()->with('1 year')->andReturnSelf();
        $mock->shouldReceive('setRefreshTokenTTL')->once()->with('2 years')->andReturnSelf();
        $mock->shouldReceive('setExtraData')->once()->with(['extra' => 'data'])->andReturnSelf();
        $mock->shouldReceive('setResponseAsCookie')->once()->with(false)->andReturnSelf();
        $mock->shouldReceive('setSingleLogin')->once()->with(false)->andReturnSelf();

        $mock->shouldReceive('setLinkUrl')->once()->with(route('auth.magic-link.validate'))->andReturnSelf();
        $mock->shouldReceive('setValidateRouteName')->once()->with('auth.magic-link.validate')->andReturnSelf();
        $mock->shouldReceive('setLinkTTL')->once()->with('5 minutes')->andReturnSelf();
        $mock->shouldReceive('setLinkMail')
            ->once()
            ->with($this->equalTo(fn (string $link) => new MagicLinkMail($link)))
            ->andReturnSelf();

        /** @psalm-var PasswordLessAuth $mock */
        new MagicLinkController($mock);
    }

    public function test_build_controller_non_default_ok(): void
    {
        $mock = $this->mock(PasswordLessAuth::class);
        $mock->shouldReceive('setAccessTokenTTL')->once()->with('1 year')->andReturnSelf();
        $mock->shouldReceive('setRefreshTokenTTL')->once()->with('2 years')->andReturnSelf();
        $mock->shouldReceive('setExtraData')->once()->with(['extra' => 'data'])->andReturnSelf();
        $mock->shouldReceive('setResponseAsCookie')->once()->with(false)->andReturnSelf();
        $mock->shouldReceive('setSingleLogin')->once()->with(false)->andReturnSelf();

        $mock->shouldReceive('setLinkUrl')->once()->with('http://localhost')->andReturnSelf();
        $mock->shouldReceive('setValidateRouteName')->once()->with('auth.magic-link.validate2')->andReturnSelf();
        $mock->shouldReceive('setLinkTTL')->once()->with('30 minutes')->andReturnSelf();
        $mock->shouldReceive('setLinkMail')
            ->once()
            ->with($this->equalTo(fn (string $link) => null))
            ->andReturnSelf();

        /** @psalm-var PasswordLessAuth $mock */
        new NonDefaultMagicLinkController($mock);
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
        $response->assertStatus(200);
        $this->assertArrayHasKey('extra', (array) json_decode((string) $response->getContent(), true));
    }

    public function test_magic_link_login_with_mail_variant_ok(): void
    {
        Mail::fake();
        $user = UserFactory::new()->create();
        ClientFactory::new()->create();

        $link = '';
        $this->postJson(route('auth.magic-link.request2'), [
            'username' => $user->username,
        ])->assertStatus(204);
        Mail::assertQueued(MagicLinkMailVariation::class, function (MagicLinkMailVariation $mail) use (&$link) {
            $link = $mail->link;
            return true;
        });
        $link = str_replace('http://localhost', 'http://localhost/auth/magic-link/validate2', $link);

        $response = $this->postJson($link, []);
        $response->assertStatus(200);
        $this->assertArrayHasKey('extra', (array) json_decode((string) $response->getContent(), true));
    }

    public function test_magic_link_login_invalid_request(): void
    {
        $response = $this->postJson(route('auth.magic-link.request'), [
            'username' => '',
        ]);
        $response->assertStatus(422);
        $this->assertEquals([
            'status' => 'Unprocessable Content',
            'message' => 'validation.summary',
            'data' => [
                'username' => ['The username field is required.'],
            ],
        ], json_decode((string) $response->getContent(), true));

        $response = $this->postJson(route('auth.magic-link.validate'), []);
        $response->assertStatus(401);
        $this->assertEquals([
            'status' => 'Unauthorized',
            'message' => 'validation.invalid-signature',
            'data' => [],
        ], json_decode((string) $response->getContent(), true));
    }
}
