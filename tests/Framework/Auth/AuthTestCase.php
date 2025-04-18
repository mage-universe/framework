<?php declare(strict_types=1);

namespace Tests\Framework\Auth;

use DateInterval;
use Illuminate\Support\Facades\Cache;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Mage\Framework\Auth\Contracts\LockInterface;
use Mage\Framework\Auth\Contracts\MagicLinkInterface;
use Mage\Framework\Auth\Contracts\PassportTokensInterface;
use Mage\Framework\Auth\Contracts\TwoFactorTokenInterface;
use Mage\Framework\Auth\Contracts\UserRepositoryInterface;
use Mage\Framework\Auth\Grants\PasswordAuthGrant;
use Mage\Framework\Auth\Grants\PasswordLessGrant;
use Mage\Framework\Auth\Repositories\LockRepository;
use Mage\Framework\Auth\Repositories\MagicLinkRepository;
use Mage\Framework\Auth\Repositories\PassportTokensRepository;
use Mage\Framework\Auth\Repositories\TwoFactorTokenRepository;
use Mage\Framework\Auth\Repositories\User;
use Mage\Framework\Auth\Repositories\UserRepository;
use phpmock\Mock;
use phpmock\MockBuilder;
use Psr\Http\Message\ServerRequestInterface;
use Tests\TestCase;

abstract class AuthTestCase extends TestCase
{
    public Mock $mockRandomToken;

    protected function getPackageProviders($app)
    {
        return [...parent::getPackageProviders($app)];
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->mockRandomToken = $this->mockRandomToken('token');
    }

    public function tearDown(): void
    {
        $this->mockRandomToken->disable();
        Cache::forget('auth.lock.test@test.com');
        parent::tearDown();
    }

    protected function mockRandomToken(string $name): Mock
    {
        $builder = (new MockBuilder())->setNamespace('Mage\Framework\Auth\Repositories')
            ->setName('bin2hex')
            ->setFunction(fn () => $name)
            ->build();
        $builder->enable();
        return $builder;
    }

    protected function getRequest(
        ?string $email,
        ?string $password,
        ?string $token = 'token',
        ?string $code = '',
        ?string $type = 'email'
    ): ServerRequestInterface {
        /** @psalm-var ServerRequestInterface $request */
        $request = $this->app?->make(ServerRequestInterface::class);
        return $request->withParsedBody([
            'client_id' => 1,
            'client_secret' => 'YIohKpRaizpUPokxEEiqZpe6hCea7fv4kTkK2BLR',
            'username' => $email,
            'password' => $password,
            'token' => $token,
            'code' => $code,
            'type' => $type,
        ]);
    }

    protected function getUserEntity(
        string $email,
        bool $isVerified = false,
        bool $isTwoFactorEmailEnabled = false,
        bool $isTwoFactorAppCodeEnabled = false,
        ?string $twoFactorSecret = null,
    ): User {
        return new User(
            '1',
            $email,
            $isVerified,
            $isTwoFactorEmailEnabled,
            $isTwoFactorAppCodeEnabled,
            $twoFactorSecret
        );
    }

    protected function getTtl(): DateInterval
    {
        return new DateInterval('PT1H');
    }

    protected function getPasswordLessGrant(): PasswordLessGrant
    {
        /** @psalm-var UserRepository $user */
        $user = $this->app?->make(UserRepositoryInterface::class);
        /** @psalm-var RefreshTokenRepository $refreshToken */
        $refreshToken = $this->app?->make(RefreshTokenRepositoryInterface::class);
        /** @psalm-var AuthorizationServer $authorizationServer */
        $authorizationServer = $this->app?->make(AuthorizationServer::class);
        /** @psalm-var PassportTokensRepository $token */
        $token = $this->app?->make(PassportTokensInterface::class);
        /** @psalm-var MagicLinkRepository $magicLink */
        $magicLink = $this->app?->make(MagicLinkInterface::class);

        $passwordLessGrant = new PasswordLessGrant($user, $refreshToken, $authorizationServer, $token, $magicLink);
        $passwordLessGrant->setAccessTokenTTL($this->getTtl());
        return $passwordLessGrant;
    }

    protected function getPasswordAuthGrant(): PasswordAuthGrant
    {
        /** @psalm-var UserRepository $user */
        $user = $this->app?->make(UserRepositoryInterface::class);
        /** @psalm-var RefreshTokenRepository $refreshToken */
        $refreshToken = $this->app?->make(RefreshTokenRepositoryInterface::class);
        /** @psalm-var AuthorizationServer $authorizationServer */
        $authorizationServer = $this->app?->make(AuthorizationServer::class);
        /** @psalm-var PassportTokensRepository $token */
        $token = $this->app?->make(PassportTokensInterface::class);
        /** @psalm-var LockRepository $lock */
        $lock = $this->app?->make(LockInterface::class);
        /** @psalm-var TwoFactorTokenRepository $twoToken */
        $twoToken = $this->app?->make(TwoFactorTokenInterface::class);

        $passwordGrant = new PasswordAuthGrant($user, $refreshToken, $authorizationServer, $token, $lock, $twoToken);
        $passwordGrant->setAccessTokenTTL($this->getTtl());
        return $passwordGrant;
    }
}
