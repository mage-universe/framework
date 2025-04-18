<?php declare(strict_types=1);

namespace Mage\Framework\Bus\Locator;

use Mage\Framework\Bus\Contracts\Query\Query;
use Mage\Framework\Bus\Contracts\Query\QueryHandler;

class QueryLocator extends Locator
{
    public function __construct(array $paths)
    {
        parent::__construct($paths, Query::class, QueryHandler::class);
    }
}
