<?php declare(strict_types=1);

namespace Mage\Framework\Bus\Bridge\Dispatcher;

use Mage\Framework\Bus\Bridge\Job\CommandAsync;
use Mage\Framework\Bus\Command\Dispatcher;
use Mage\Framework\Bus\Contracts\Command\Command;
use Mage\Framework\Bus\Contracts\Query\Query;
use Mage\Framework\Bus\Locator\Locator;

final readonly class CommandDispatcher implements Dispatcher
{
    public function __construct(
        private \Illuminate\Contracts\Bus\Dispatcher $dispatcher,
        Locator $locator
    ) {
        $this->dispatcher->map($locator->mappings());
    }

    public function dispatch(Command|Query $command): mixed
    {
        return $this->dispatcher->dispatch($command);
    }

    public function asyncDispatch(Command $command, string $queue): void
    {
        $this->dispatcher->dispatch(new CommandAsync($command, $queue));
    }
}
