<?php declare(strict_types=1);

namespace Mage\Framework\Wiring\Bridge;

use Illuminate\Support\ServiceProvider;
use Mage\Framework\Console\Command;
use Mage\Framework\Console\Schedule;
use Mage\Framework\Wiring\Locator\BindingsLocator;
use Mage\Framework\Wiring\Locator\CommandLocator;
use Mage\Framework\Wiring\Locator\MigrationsLocator;
use Mage\Framework\Wiring\Locator\RoutesLocator;
use function Lambdish\Phunctional\map;

abstract class WiringServiceProvider extends ServiceProvider
{
    abstract protected function bindingsPaths(): array;
    abstract protected function commandsPaths(): array;
    abstract protected function routesPaths(): array;
    abstract protected function migrationsPaths(): array;

    /** @psalm-api */
    public function boot(): void
    {
        map(function (string $concrete, string $abstract): void {
            $this->app->bindIf($abstract, $concrete);
        }, (new BindingsLocator($this->bindingsPaths()))->bindings());

        map(function (string $file): void {
            $this->loadRoutesFrom($file);
        }, (new RoutesLocator($this->routesPaths()))->routes());

        $this->loadMigrationsFrom((new MigrationsLocator($this->migrationsPaths()))->migrations());

        $commands = (new CommandLocator($this->commandsPaths()))->commands();
        $this->commands($commands);

        /** @psalm-var \Illuminate\Console\Scheduling\Schedule $schedule */
        $schedule = $this->app->make('Illuminate\Console\Scheduling\Schedule');
        $scheduler = new Schedule($schedule);

        map(function (string $command) use ($scheduler): void {
            /** @psalm-var Command $command */
            $command::schedule($scheduler);
        }, $commands);
    }
}
