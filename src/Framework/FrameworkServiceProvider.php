<?php declare(strict_types=1);

namespace Mage\Framework;

use Illuminate\Support\ServiceProvider;
use Mage\Framework\Auth\AuthServiceProvider;
use Mage\Framework\Bus\BusServiceProvider;
use Mage\Framework\Config\ConfigServiceProvider;
use Mage\Framework\Http\Middlewares\InvalidSignature\ValidateSignature;
use Mage\Framework\Wiring\WiringServiceProvider;
use function Lambdish\Phunctional\map;

/** @infection-ignore-all */
final class FrameworkServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->register(ConfigServiceProvider::class);
        $this->app->register(AuthServiceProvider::class);
        $this->app->register(BusServiceProvider::class);
        $this->app->register(WiringServiceProvider::class);

        map(function (string $provider): void {
            $this->app->register($provider, true);
        }, ServiceProvider::defaultProviders()->toArray());
    }

    public function register(): void
    {
        /** @psalm-var \Illuminate\Routing\Router $router */
        $router = $this->app['router'];
        $router->aliasMiddleware('signed', ValidateSignature::class);
    }
}
