<?php declare(strict_types=1);

namespace Tests\Framework\Bus\Query;

use Error;
use Mage\Framework\Bus\Command\QueryBus;
use Mage\Framework\Bus\Contracts\Query\Query;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use ReflectionException;
use Tests\Framework\Bus\BusTestCase;
use Tests\Framework\Bus\Data\AnotherValidCase\Context\Application\AnotherValidQuery;
use Tests\Framework\Bus\Data\InvalidTypeCase\Context\Application\InvalidQuery;
use Tests\Framework\Bus\Data\InvalidTypeCase\Context\Application\InvalidQueryHandler;
use Tests\Framework\Bus\Data\ValidCase\Context\Application\ValidQuery;

final class QueryBusTest extends BusTestCase
{
    #[DefineEnvironment('usesValidBus')]
    public function test_handler_got_called_by_query(): void
    {
        $bus = $this->getQueryBus();
        $this->assertEquals('test', $bus->handle(new ValidQuery()));
        $this->assertEquals(5, $bus->handle(new AnotherValidQuery()));
    }

    #[DefineEnvironment('usesInvalidQueryBus')]
    public function test_query_handler_arguments_not_implement_query_interface(): void
    {
        $queryInterface = Query::class;
        $queryHandler = InvalidQueryHandler::class;
        $message = "Method argument in $queryHandler::__invoke() implementing $queryInterface not detected";

        $this->expectException(ReflectionException::class);
        $this->expectExceptionMessage(preg_replace('/\s+/', ' ', $message));
        $bus = $this->getQueryBus();
        $bus->handle(new InvalidQuery());
    }

    #[DefineEnvironment('usesEmptyPatternBus')]
    public function test_empty_pattern_produces_error(): void
    {
        $this->expectException(Error::class);
        $bus = $this->getQueryBus();
        $bus->handle(new ValidQuery());
    }

    private function getQueryBus(): QueryBus
    {
        /** @psalm-var QueryBus $queryBus */
        $queryBus = $this->app?->make(QueryBus::class);
        return $queryBus;
    }
}
