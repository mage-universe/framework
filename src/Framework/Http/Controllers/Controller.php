<?php declare(strict_types=1);

namespace Mage\Framework\Http\Controllers;

use Mage\Framework\Bus\Command\CommandBus;
use Mage\Framework\Bus\Command\QueryBus;

/** @psalm-api */
abstract class Controller
{
    public function __construct(
        protected CommandBus $command,
        protected QueryBus $query
    ) {}
}
