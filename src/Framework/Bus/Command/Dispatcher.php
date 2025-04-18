<?php declare(strict_types=1);

namespace Mage\Framework\Bus\Command;

use Mage\Framework\Bus\Contracts\Command\Command;
use Mage\Framework\Bus\Contracts\Query\Query;

interface Dispatcher
{
    public function dispatch(Command|Query $command): mixed;
    public function asyncDispatch(Command $command, string $queue): void;
}
