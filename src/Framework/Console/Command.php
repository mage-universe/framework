<?php declare(strict_types=1);

namespace Mage\Framework\Console;

use Mage\Framework\Bus\Command\CommandBus;
use Mage\Framework\Bus\Command\QueryBus;
use Mage\Framework\Bus\Event\EventBus;

/** @psalm-suppress PropertyNotSetInConstructor */
abstract class Command extends \Illuminate\Console\Command
{
    public function __construct(
        protected readonly CommandBus $command,
        protected readonly QueryBus $query,
        protected readonly EventBus $event
    ) {
        parent::__construct();
    }

    abstract public static function schedule(Schedule $scheduler): void;
}
