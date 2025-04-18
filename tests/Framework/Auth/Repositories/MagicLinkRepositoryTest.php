<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Repositories;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Mage\Framework\Auth\Exceptions\AuthException;
use Mage\Framework\Auth\Mail\MagicLinkMail;
use Mage\Framework\Auth\Repositories\MagicLinkRepository;
use Tests\Framework\Auth\AuthTestCase;
use Tests\Framework\Auth\Data\Controllers\MagicLinkController;

class MagicLinkRepositoryTest extends AuthTestCase
{
    protected function defineRoutes($router): void
    {
        $router->post('auth/magic-link', [MagicLinkController::class, '_login'])
            ->name('auth.magic-link.request');
        $router->post('auth/magic-link/validate', [MagicLinkController::class, '_validate'])
            ->name('auth.magic-link.validate');
    }

    public function test_request_magic_link_and_validate_token(): void
    {
        Mail::fake();

        $request = $this->getRequest('test@test.com', 'password');
        $user = $this->getUserEntity('test@test.com');

        $magicLinkRepository = new MagicLinkRepository();
        $generatedLink = null;
        $url = 'http://localhost';
        $magicLinkRepository->queueMagicLinkToEmail(
            $request,
            $user,
            $url,
            'auth.magic-link.validate',
            $this->getTtl(),
            function (string $link) use (&$generatedLink): MagicLinkMail {
                $generatedLink = $link;
                return new MagicLinkMail($link);
            }
        );

        /** @psalm-var string $generatedLink */
        $this->assertStringContainsString($url . '?email=' . urlencode('test@test.com'), $generatedLink);
        $this->assertStringContainsString('token=token', $generatedLink);
        $this->assertStringContainsString('signature=', $generatedLink);
        $this->assertStringContainsString('expires=', $generatedLink);

        Mail::assertQueued(MagicLinkMail::class);
        $this->assertTrue(Cache::has('auth.magic-link.request.token'));

        /** @psalm-var string $encryptedRequest */
        $encryptedRequest = Cache::get('auth.magic-link.request.token');
        $this->assertEquals([
            'request' => serialize($request),
            'user' => serialize($user),
        ], decrypt($encryptedRequest));

        $originalRequest = $magicLinkRepository->validateMagicLinkToken($request);
        $this->assertEquals($request->getParsedBody(), $originalRequest->getParsedBody());
        $this->assertFalse(Cache::has('auth.magic-link.request.token'));
    }

    public function test_request_magic_link_validation_with_invalid_token_throws_exception(): void
    {
        $this->expectExceptionMessage(AuthException::invalidToken()->getMessage());
        $request = $this->getRequest('test@test.com', 'password', 'invalid-token');

        $magicLinkRepository = new MagicLinkRepository();
        $magicLinkRepository->validateMagicLinkToken($request);
    }

    public function test_request_magic_link_validation_with_null_token_throws_exception(): void
    {
        $this->expectExceptionMessage(AuthException::invalidToken()->getMessage());
        $request = $this->getRequest('test@test.com', 'password', null);

        $magicLinkRepository = new MagicLinkRepository();
        $magicLinkRepository->validateMagicLinkToken($request);
    }
}
