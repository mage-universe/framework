<?php declare(strict_types=1);

namespace Mage\Framework\Wiring;

/** @infection-ignore-all */
final class WiringServiceProvider extends Bridge\WiringServiceProvider
{
    protected function bindingsPaths(): array
    {
        return [[
            'path' => dirname(__DIR__, 4) . '/src',
            'pattern' => '/\/Infrastructure\/[^\/]+$/',
        ], config('mage.wiring.bindings', [])];
    }

    protected function commandsPaths(): array
    {
        return [[
            'path' => dirname(__DIR__, 4) . '/src',
            'pattern' => '/^.*\/Infrastructure\/Console\/[^\/]+$/',
        ], config('mage.wiring.commands', [])];
    }

    protected function routesPaths(): array
    {
        return [[
            'path' => dirname(__DIR__, 4) . '/src',
            'pattern' => '/^.*\/Infrastructure\/Routes\/[^\/]+$/',
        ], config('mage.wiring.routes', [])];
    }

    protected function migrationsPaths(): array
    {
        return [[
            'path' => dirname(__DIR__, 4) . '/src',
            'pattern' => '/^.*\/Infrastructure\/Persistence\/(?:\w+\/)*Migrations\/[^\/]+$/',
        ], config('mage.wiring.migrations', [])];
    }
}
