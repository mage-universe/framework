<?php declare(strict_types=1);

namespace Tests\Framework\Auth\Repositories;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Mage\Framework\Auth\Exceptions\AuthException;
use Mage\Framework\Auth\Mail\TwoFactorEmail;
use Mage\Framework\Auth\Repositories\TwoFactorTokenRepository;
use RobThree\Auth\TwoFactorAuth;
use Tests\Framework\Auth\AuthTestCase;

class TwoFactorTokenRepositoryTest extends AuthTestCase
{
    public function test_two_factor_temporal_token_types(): void
    {
        $request = $this->getRequest('test@test.com', 'password');
        $user = $this->getUserEntity('test@test.com', isTwoFactorEmailEnabled: true);
        $token = (new TwoFactorTokenRepository())->twoFactorTemporalToken($request, $user, $this->getTtl());
        $this->assertEquals(['email'], $token['types']);

        $user = $this->getUserEntity(
            'test@test.com',
            isTwoFactorEmailEnabled: true,
            isTwoFactorAppCodeEnabled: true,
            twoFactorSecret: 'some-code'
        );
        $token = (new TwoFactorTokenRepository())->twoFactorTemporalToken($request, $user, $this->getTtl());
        $this->assertEquals(['email', 'code'], $token['types']);
    }

    public function test_two_factor_temporal_token(): void
    {
        $request = $this->getRequest('test@test.com', 'password');
        $user = $this->getUserEntity('test@test.com');
        $token = (new TwoFactorTokenRepository())->twoFactorTemporalToken($request, $user, $this->getTtl());

        $this->assertEquals(3600, $token['expires_in']);
        $this->assertEquals('token', $token['token']);

        /** @psalm-var string $encryptedRequest */
        $encryptedRequest = Cache::get('auth.two-steps.request.token');
        $this->assertEquals([
            'request' => serialize($request),
            'user' => serialize($user),
        ], decrypt($encryptedRequest));
    }

    public function test_queue_email_two_factor_token(): void
    {
        Mail::fake();

        $request = $this->getRequest('test@test.com', 'password');
        $user = $this->getUserEntity('test@test.com');
        (new TwoFactorTokenRepository())->twoFactorTemporalToken($request, $user, $this->getTtl());

        $generatedCode = null;
        (new TwoFactorTokenRepository())->queueTwoFactorTokenToEmail(
            $request,
            $this->getTtl(),
            function (string $code) use (&$generatedCode): TwoFactorEmail {
                $generatedCode = $code;
                return new TwoFactorEmail($code);
            }
        );

        $this->assertIsString($generatedCode);
        $this->assertTrue(strlen($generatedCode) === 6);
        $this->assertTrue($generatedCode >= 0);
        $this->assertTrue($generatedCode <= 999999);
        $this->assertTrue(Cache::has('auth.two-steps.email.test@test.com'));
        Mail::assertQueued(TwoFactorEmail::class);
    }

    public function test_validate_two_factor_token(): void
    {
        Mail::fake();

        $request = $this->getRequest('test@test.com', 'password');
        $user = $this->getUserEntity('test@test.com');
        $twoFactorTokenRepository = new TwoFactorTokenRepository();
        $twoFactorTokenRepository->twoFactorTemporalToken($request, $user, $this->getTtl());

        $generatedCode = null;
        $twoFactorTokenRepository->queueTwoFactorTokenToEmail(
            $request,
            $this->getTtl(),
            function (string $code) use (&$generatedCode): TwoFactorEmail {
                $generatedCode = $code;
                return new TwoFactorEmail($code);
            }
        );

        $request2 = $this->getRequest('test@test.com', 'password', 'token', $generatedCode, 'email');
        $originalRequest = $twoFactorTokenRepository->validateTwoFactorToken($request2);

        $this->assertFalse(Cache::has('auth.two-steps.request.token'));
        $this->assertFalse(Cache::has('auth.two-steps.email.test@test.com'));

        $this->assertEquals($request->getParsedBody(), $originalRequest->getParsedBody());
    }

    public function test_validate_two_factor_wrong_type(): void
    {
        $this->expectExceptionMessage(AuthException::invalidType()->getMessage());
        $request = $this->getRequest('test@test.com', 'password', 'token', '1234', 'wrong-type');
        $twoFactorTokenRepository = $this->getRequestedEmailToken();
        $twoFactorTokenRepository->validateTwoFactorToken($request);
    }

    public function test_validate_two_factor_empty_code(): void
    {
        $this->expectExceptionMessage(AuthException::invalidTwoFactorCode()->getMessage());
        $request = $this->getRequest('test@test.com', 'password', 'token', null, 'email');
        $twoFactorTokenRepository = $this->getRequestedEmailToken();
        $twoFactorTokenRepository->validateTwoFactorToken($request);
    }

    public function test_validate_two_factor_invalid_code(): void
    {
        $this->expectExceptionMessage(AuthException::invalidTwoFactorCode()->getMessage());
        $request = $this->getRequest('test@test.com', 'password', 'token', '123456', 'email');
        $twoFactorTokenRepository = $this->getRequestedEmailToken();
        $twoFactorTokenRepository->validateTwoFactorToken($request);
    }

    public function test_validate_two_factor_app_code(): void
    {
        $request = $this->getRequest('test@test.com', 'password', 'token');
        $auth = new TwoFactorAuth();
        $code = $auth->getCode('C4ALOOZAJB45JSLU');
        $user = $this->getUserEntity('test@test.com', twoFactorSecret: 'C4ALOOZAJB45JSLU');

        $twoFactorTokenRepository = new TwoFactorTokenRepository();
        $twoFactorTokenRepository->twoFactorTemporalToken($request, $user, $this->getTtl());

        $request = $this->getRequest('test@test.com', 'password', 'token', $code, 'code');
        $twoFactorTokenRepository->validateTwoFactorToken($request);
    }

    public function test_two_factor_app_code_invalid(): void
    {
        $this->expectExceptionMessage(AuthException::invalidTwoFactorCode()->getMessage());

        $request = $this->getRequest('test@test.com', 'password');
        $user = $this->getUserEntity('test@test.com', twoFactorSecret: 'C4ALOOZAJB45JSLU');

        $twoFactorTokenRepository = new TwoFactorTokenRepository();
        $twoFactorTokenRepository->twoFactorTemporalToken($request, $user, $this->getTtl());

        $request = $this->getRequest('test@test.com', 'password', 'token', 'fgf', 'code');
        $twoFactorTokenRepository->validateTwoFactorToken($request);
    }

    private function getRequestedEmailToken(): TwoFactorTokenRepository
    {
        Mail::fake();
        $request = $this->getRequest('test@test.com', 'password');
        $user = $this->getUserEntity('test@test.com');
        $twoFactorTokenRepository = new TwoFactorTokenRepository();
        $twoFactorTokenRepository->twoFactorTemporalToken($request, $user, $this->getTtl());
        $twoFactorTokenRepository->queueTwoFactorTokenToEmail(
            $request,
            $this->getTtl(),
            function (string $code): TwoFactorEmail {
                return new TwoFactorEmail($code);
            }
        );
        return $twoFactorTokenRepository;
    }
}
