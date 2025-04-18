<?php declare(strict_types=1);

namespace Mage\Framework\Auth;

use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Passport;
use Laravel\Passport\PassportServiceProvider;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Mage\Framework\Auth\Contracts\LockInterface;
use Mage\Framework\Auth\Contracts\MagicLinkInterface;
use Mage\Framework\Auth\Contracts\PassportTokensInterface;
use Mage\Framework\Auth\Contracts\TwoFactorTokenInterface;
use Mage\Framework\Auth\Contracts\UserRepositoryInterface;
use Mage\Framework\Auth\PasswordBroker\PasswordResetServiceProvider;
use Mage\Framework\Auth\Repositories\LockRepository;
use Mage\Framework\Auth\Repositories\MagicLinkRepository;
use Mage\Framework\Auth\Repositories\PassportTokensRepository;
use Mage\Framework\Auth\Repositories\TwoFactorTokenRepository;
use Mage\Framework\Auth\Repositories\UserRepository;

final class AuthServiceProvider extends PassportServiceProvider
{
    public function boot(): void
    {
        $this->deleteCookieOnLogout();
    }

    public function register(): void
    {
        Passport::ignoreRoutes();
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/Views', 'Auth');
        parent::register();

        $this->app->register(PasswordResetServiceProvider::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(RefreshTokenRepositoryInterface::class, RefreshTokenRepository::class);
        $this->app->bind(PassportTokensInterface::class, PassportTokensRepository::class);
        $this->app->bind(MagicLinkInterface::class, MagicLinkRepository::class);
        $this->app->bind(TwoFactorTokenInterface::class, TwoFactorTokenRepository::class);
        $this->app->bind(LockInterface::class, LockRepository::class);
    }
}
