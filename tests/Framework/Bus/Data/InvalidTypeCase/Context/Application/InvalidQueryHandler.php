<?php declare(strict_types=1);

namespace Tests\Framework\Bus\Data\InvalidTypeCase\Context\Application;

use Mage\Framework\Bus\Contracts\Query\QueryHandler;

/** @psalm-api */
final class InvalidQueryHandler implements QueryHandler
{
    /** @psalm-param mixed $query */
    public function __invoke($query): mixed
    {
        return 2 * 2;
    }
}
