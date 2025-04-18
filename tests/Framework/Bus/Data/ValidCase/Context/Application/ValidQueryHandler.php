<?php declare(strict_types=1);

namespace Tests\Framework\Bus\Data\ValidCase\Context\Application;

use Mage\Framework\Bus\Contracts\Query\QueryHandler;

/** @psalm-api */
final class ValidQueryHandler implements QueryHandler
{
    public function __invoke(ValidQuery $query): mixed
    {
        return 'test';
    }
}
