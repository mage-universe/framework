<?php declare(strict_types=1);

namespace Tests\Framework\Bus\Data;

use Mage\Framework\Bus\Bridge\BusServiceProvider;

class EmptyPatternBusServiceProvider extends BusServiceProvider
{
    protected function commandPaths(): array
    {
        return [[]];
    }

    protected function queryPaths(): array
    {
        return [[
            'path' => dirname(__DIR__) . '/Data/InvalidTypeCase',
            'pattern' => '',
        ]];
    }

    protected function eventPaths(): array
    {
        return [[]];
    }

    protected function commandMiddlewares(): array
    {
        return [[]];
    }

    protected function queryMiddlewares(): array
    {
        return [[]];
    }

    protected function eventMiddlewares(): array
    {
        return [[]];
    }
}
