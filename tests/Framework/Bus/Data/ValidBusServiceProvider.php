<?php declare(strict_types=1);

namespace Tests\Framework\Bus\Data;

use Mage\Framework\Bus\Bridge\BusServiceProvider;

class ValidBusServiceProvider extends BusServiceProvider
{
    protected function commandPaths(): array
    {
        return [[
            'path' => dirname(__DIR__) . '/Data/ValidCase',
            'pattern' => '/^.*\/Application\/[^\/]+$/',
        ]];
    }

    protected function queryPaths(): array
    {
        return [[
            'path' => dirname(__DIR__) . '/Data/ValidCase',
            'pattern' => '/^.*\/Application\/[^\/]+$/',
        ], [
            'path' => dirname(__DIR__) . '/Data/AnotherValidCase',
            'pattern' => '/^.*\/Application\/[^\/]+$/',
        ]];
    }

    protected function eventPaths(): array
    {
        return [[
            'path' => dirname(__DIR__) . '/Data/ValidCase',
            'pattern' => '/^.*\/Application\/[^\/]+$/',
        ], [
            'path' => dirname(__DIR__) . '/Data/AnotherValidCase',
            'pattern' => '/^.*\/Application\/[^\/]+$/',
        ]];
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
