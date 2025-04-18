<?php declare(strict_types=1);

namespace Mage\Framework\Bus\Command;

use Mage\Framework\Bus\Command\Middleware\DispatcherMiddleware;
use Mage\Framework\Bus\Command\Middleware\Middleware;
use Mage\Framework\Bus\Contracts\Command\Command;
use Mage\Framework\Bus\Contracts\Query\Query;

/** @psalm-api */
abstract class Bus
{
    /** @psalm-param array<Middleware> $middlewares */
    public function __construct(
        private readonly Dispatcher $dispatcher,
        private array $middlewares
    ) {
        $this->middlewares[] = new DispatcherMiddleware($this->dispatcher);
    }

    protected function dispatch(Command|Query $command): mixed
    {
        return $this->middlewareChain($this->middlewares)($command);
    }

    protected function asyncDispatch(Command $command, string $queue): void
    {
        $this->dispatcher->asyncDispatch($command, $queue);
    }

    /** @psalm-param array<Middleware> $middlewares */
    private function middlewareChain(array $middlewares): callable
    {
        $lastCallable = static fn (): null => null;
        while ($middleware = array_pop($middlewares)) {
            $lastCallable = static fn (Command|Query $command): mixed => $middleware->handle($command, $lastCallable);
        }
        return $lastCallable;
    }
}
