<?php declare(strict_types=1);

namespace Mage\Framework\Bus\Event;

use Mage\Framework\Bus\Contracts\Event\AsyncEvent;
use Mage\Framework\Bus\Contracts\Event\Event;
use Mage\Framework\Bus\Event\Middleware\DispatcherMiddleware;
use Mage\Framework\Bus\Event\Middleware\Middleware;

/** @psalm-api */
final class EventBus
{
    /** @psalm-param array<Middleware> $middlewares */
    public function __construct(
        private readonly Dispatcher $dispatcher,
        private array $middlewares
    ) {
        $this->middlewares[] = new DispatcherMiddleware($this->dispatcher);
    }

    public function dispatch(AsyncEvent|Event $event): void
    {
        if (in_array(AsyncEvent::class, class_implements($event))) {
            /** @psalm-var AsyncEvent $event */
            $this->dispatcher->asyncDispatch($event, $event->queue());
        } else {
            $this->dispatchNow($event);
        }
    }

    public function dispatchNow(Event $event): void
    {
        $this->middlewareChain($this->middlewares)($event);
    }

    /** @psalm-param array<Middleware> $middlewares */
    private function middlewareChain(array $middlewares): callable
    {
        $lastCallable = static fn (): null => null;
        while ($middleware = array_pop($middlewares)) {
            $lastCallable = static fn (Event $event): mixed => $middleware->handle($event, $lastCallable);
        }
        return $lastCallable;
    }
}
