<?php declare(strict_types=1);

namespace Tests\Framework\Bus\Data\AnotherValidCase\Context\Application;

use Mage\Framework\Bus\Contracts\Query\QueryHandler;

/** @psalm-api */
final class DifferentNameForHandler implements QueryHandler
{
    public function __invoke(AnotherValidQuery $query): int
    {
        return 5;
    }
}
