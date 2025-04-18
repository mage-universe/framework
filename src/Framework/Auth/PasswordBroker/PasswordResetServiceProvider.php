<?php declare(strict_types=1);

namespace Mage\Framework\Auth\PasswordBroker;

/** @psalm-suppress all */
class PasswordResetServiceProvider extends \Illuminate\Auth\Passwords\PasswordResetServiceProvider
{
    protected function registerPasswordBroker(): void
    {
        $this->app->singleton('auth.password', function ($app) {
            return new PasswordBrokerManager($app);
        });

        $this->app->bind('auth.password.broker', function ($app) {
            return $app->make('auth.password')->broker();
        });
    }
}
