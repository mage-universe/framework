<?php declare(strict_types=1);

namespace Tests\Framework\Bus;

use Illuminate\Foundation\Application;
use Tests\Framework\Bus\Data\EmptyPatternBusServiceProvider;
use Tests\Framework\Bus\Data\InvalidBusServiceProvider;
use Tests\Framework\Bus\Data\ValidBusServiceProvider;
use Tests\TestCase;

abstract class BusTestCase extends TestCase
{
    /** @psalm-api */
    protected function usesValidBus(Application $app): void
    {
        $app->register(ValidBusServiceProvider::class);
    }

    /** @psalm-api */
    protected function usesInvalidQueryBus(Application $app): void
    {
        $app->register(InvalidBusServiceProvider::class);
    }

    /** @psalm-api */
    protected function usesEmptyPatternBus(Application $app): void
    {
        $app->register(EmptyPatternBusServiceProvider::class);
    }
}
