<?php declare(strict_types=1);

namespace Mage\Framework\Bus\Bridge;

use Illuminate\Support\ServiceProvider;
use Mage\Framework\Bus\Bridge\Dispatcher\CommandDispatcher;
use Mage\Framework\Bus\Bridge\Dispatcher\EventDispatcher;
use Mage\Framework\Bus\Command\CommandBus;
use Mage\Framework\Bus\Command\QueryBus;
use Mage\Framework\Bus\Event\EventBus;
use Mage\Framework\Bus\Locator\CommandLocator;
use Mage\Framework\Bus\Locator\EventLocator;
use Mage\Framework\Bus\Locator\QueryLocator;

abstract class BusServiceProvider extends ServiceProvider
{
    abstract protected function commandPaths(): array;

    abstract protected function queryPaths(): array;

    abstract protected function eventPaths(): array;

    abstract protected function commandMiddlewares(): array;

    abstract protected function queryMiddlewares(): array;

    abstract protected function eventMiddlewares(): array;

    public function register(): void
    {
        /** @psalm-var \Illuminate\Bus\Dispatcher $busDispatcher */
        $busDispatcher = $this->app->make(\Illuminate\Bus\Dispatcher::class);

        /** @psalm-var \Illuminate\Events\Dispatcher $eventDispatcher */
        $eventDispatcher = $this->app->make(\Illuminate\Events\Dispatcher::class);

        $this->registerCommandBus($busDispatcher);
        $this->registerQueryBus($busDispatcher);
        $this->registerEventBus($busDispatcher, $eventDispatcher);
    }

    private function registerCommandBus(\Illuminate\Bus\Dispatcher $busDispatcher): void
    {
        $this->app->when(CommandBus::class)->needs(\Mage\Framework\Bus\Command\Dispatcher::class)->give(
            fn (): CommandDispatcher => new CommandDispatcher($busDispatcher, new CommandLocator($this->commandPaths()))
        );
        $this->app->when(CommandBus::class)->needs('$middlewares')->give(fn () => $this->commandMiddlewares());
    }
    private function registerQueryBus(\Illuminate\Bus\Dispatcher $busDispatcher): void
    {
        $this->app->when(QueryBus::class)->needs(\Mage\Framework\Bus\Command\Dispatcher::class)->give(
            fn (): CommandDispatcher => new CommandDispatcher($busDispatcher, new QueryLocator($this->queryPaths()))
        );
        $this->app->when(QueryBus::class)->needs('$middlewares')->give(fn () => $this->queryMiddlewares());
    }
    private function registerEventBus(
        \Illuminate\Bus\Dispatcher $busDispatcher,
        \Illuminate\Events\Dispatcher $eventDispatcher
    ): void {
        $this->app->bind(
            \Mage\Framework\Bus\Contracts\Event\EventPublisher::class,
            \Mage\Framework\Bus\Event\EventPublisher::class
        );
        $this->app->when(EventBus::class)->needs(\Mage\Framework\Bus\Event\Dispatcher::class)->give(
            fn (): EventDispatcher => new EventDispatcher($busDispatcher, $eventDispatcher, new EventLocator(
                $this->eventPaths()
            ))
        );
        $this->app->when(EventBus::class)->needs('$middlewares')->give(fn () => $this->eventMiddlewares());
    }
}
