<?php declare(strict_types=1);

namespace Mage\Framework\Bus\Command\Middleware;

use Closure;
use Mage\Framework\Bus\Command\Dispatcher;
use Mage\Framework\Bus\Contracts\Command\Command;
use Mage\Framework\Bus\Contracts\Query\Query;

readonly class DispatcherMiddleware implements Middleware
{
    public function __construct(private Dispatcher $dispatcher) {}

    public function handle(Command|Query $command, Closure $next): mixed
    {
        return $this->dispatcher->dispatch($command);
    }
}
