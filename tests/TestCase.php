<?php declare(strict_types=1);

namespace Tests;

use Illuminate\Config\Repository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mage\Framework\Auth\AuthServiceProvider;
use Mage\Framework\Config\ConfigServiceProvider;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use MockeryPHPUnitIntegration;
    use RefreshDatabase;

    protected $enablesPackageDiscoveries = false;

    protected function defineEnvironment($app)
    {
        tap($app['config'], function (Repository $config) {
            $config->set('cache.default', 'file');
            $config->set('database.default', 'sqlite');
            $config->set('queue.default', 'sync');
            if (!file_exists((string) $config->get('database.connections.sqlite.database'))) {
                touch((string) $config->get('database.connections.sqlite.database'));
            }
        });
    }

    protected function getPackageProviders($app)
    {
        return [ConfigServiceProvider::class, AuthServiceProvider::class, ];
    }
}
