<?php declare(strict_types=1);

namespace Mage\Framework\Config;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Foundation\CachesConfiguration;
use Illuminate\Support\ServiceProvider;
use function Lambdish\Phunctional\map;

final class ConfigServiceProvider extends ServiceProvider
{
    /**
     * @psalm-api
     * @infection-ignore-all
     */
    public function boot(): void
    {
        $this->publishes([
            dirname(__DIR__, 3) . '/config/mage.bus.php' => config_path('mage.bus.php'),
        ], 'mage.bus');
        $this->publishes([
            dirname(__DIR__, 3) . '/config/mage.wiring.php' => config_path('mage.wiring.php'),
        ], 'mage.wiring');
    }

    /** @infection-ignore-all */
    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__, 3) . '/config/auth.php', 'auth');
        $this->mergeConfigFrom(dirname(__DIR__, 3) . '/config/database.php', 'database');
        $this->mergeConfigFrom(dirname(__DIR__, 3) . '/config/cache.php', 'cache');
        $this->mergeConfigFrom(dirname(__DIR__, 3) . '/config/mail.php', 'mail');
        $this->mergeConfigFrom(dirname(__DIR__, 3) . '/config/queue.php', 'queue');
        $this->mergeConfigFrom(dirname(__DIR__, 3) . '/config/session.php', 'session');
        $this->mergeConfigFrom(dirname(__DIR__, 3) . '/config/mage.bus.php', 'mage.bus');
        $this->mergeConfigFrom(dirname(__DIR__, 3) . '/config/mage.wiring.php', 'mage.wiring');
    }

    /** @infection-ignore-all */
    protected function mergeConfigFrom($path, $key): void
    {
        if (!($this->app instanceof CachesConfiguration && $this->app->configurationIsCached())) {
            /** @psalm-var Config $config */
            $config = $this->app->make('config');

            if (is_file($path)) {
                /** @psalm-var array $path */
                $path = require $path;
                $this->searchProperty($path, $key, $config);
            }
        }
    }

    private function searchProperty(array $path, string $key, Config $config): void
    {
        map(function (mixed $value, int|string $prop) use ($config, $key): void {
            $property = $key . '.' . $prop;
            if (is_array($value)) {
                $this->searchProperty($value, $property, $config);
            } else {
                $config->set($property, $value);
            }
        }, $path);
    }
}
