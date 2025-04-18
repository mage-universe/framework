<?php declare(strict_types=1);

namespace Tests\Framework\Wiring\Data;

class TestWiringServiceProvider extends \Mage\Framework\Wiring\Bridge\WiringServiceProvider
{
    protected function bindingsPaths(): array
    {
        return [[
            'path' => dirname(__DIR__) . '/Data',
            'pattern' => '/^.*\/Infrastructure\/Persistence\/(?:\w+\/)*Repositories\/[^\/]+$/',
        ]];
    }

    protected function routesPaths(): array
    {
        return [[
            'path' => dirname(__DIR__) . '/Data',
            'pattern' => '/^.*\/Infrastructure\/Routes\/[^\/]+$/',
        ]];
    }

    protected function migrationsPaths(): array
    {
        return [[
            'path' => dirname(__DIR__) . '/Data',
            'pattern' => '/^.*\/Infrastructure\/Persistence\/(?:\w+\/)*Migrations\/[^\/]+$/',
        ]];
    }

    protected function commandsPaths(): array
    {
        return [[
            'path' => dirname(__DIR__) . '/Data',
            'pattern' => '/^.*\/Infrastructure\/Console\/[^\/]+$/',
        ]];
    }
}
