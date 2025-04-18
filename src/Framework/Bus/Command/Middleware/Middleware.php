<?php declare(strict_types=1);

namespace Mage\Framework\Bus\Command\Middleware;

use Closure;
use Mage\Framework\Bus\Contracts\Command\Command;
use Mage\Framework\Bus\Contracts\Query\Query;

interface Middleware
{
    public function handle(Command|Query $command, Closure $next): mixed;
}
