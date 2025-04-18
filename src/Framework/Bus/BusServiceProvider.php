<?php declare(strict_types=1);

namespace Mage\Framework\Bus;

/** @infection-ignore-all */
final class BusServiceProvider extends Bridge\BusServiceProvider
{
    private function config(): array
    {
        return [[
            'path' => dirname(__DIR__, 4) . '/src',
            'pattern' => '/^.*\/Application\/[^\/]+$/',
        ], config('mage.bus.paths', [])];
    }

    protected function commandPaths(): array
    {
        return $this->config();
    }

    protected function queryPaths(): array
    {
        return $this->config();
    }

    protected function eventPaths(): array
    {
        return $this->config();
    }

    protected function commandMiddlewares(): array
    {
        /** @psalm-var array */
        return config('mage.bus.command.middlewares', []);
    }

    protected function queryMiddlewares(): array
    {
        /** @psalm-var array */
        return config('mage.bus.query.middlewares', []);
    }

    protected function eventMiddlewares(): array
    {
        /** @psalm-var array */
        return config('mage.bus.event.middlewares', []);
    }
}
