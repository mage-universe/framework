<?php declare(strict_types=1);

namespace Tests\Framework\Wiring;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Route;
use Tests\Framework\Bus\Data\ValidBusServiceProvider;
use Tests\Framework\Wiring\Data\Context1\Domain\Contracts\Test1Interface;
use Tests\Framework\Wiring\Data\Context1\Domain\Contracts\Test2Interface;
use Tests\Framework\Wiring\Data\Context1\Infrastructure\Console\MyCommand;
use Tests\Framework\Wiring\Data\Context1\Infrastructure\Persistence\MongoDB\Repositories\Test1Repository;
use Tests\Framework\Wiring\Data\Context1\Infrastructure\Persistence\MySQL\Repositories\Test2Repository;
use Tests\Framework\Wiring\Data\Context1\Infrastructure\Persistence\MySQL\Repositories\Test3Repository;
use Tests\Framework\Wiring\Data\Context2\Domain\Contracts\Test3Interface;
use Tests\Framework\Wiring\Data\Context2\Infrastructure\Console\MyCommand2;
use Tests\Framework\Wiring\Data\TestWiringServiceProvider;
use Tests\TestCase;
use function Lambdish\Phunctional\reduce;

class WiringTest extends TestCase
{
    public function test_route_file_are_loaded(): void
    {
        $this->assertTrue(Route::has('context1'));
        $this->assertTrue(Route::has('context2'));
    }

    public function test_migrations_files_are_loaded(): void
    {
        /** @psalm-var \Illuminate\Database\Migrations\Migrator $migrator */
        $migrator = $this->app?->make('migrator');

        $paths = $migrator->paths();
        $this->assertContains(
            '/app/tests/Framework/Wiring/Data/Context1/Infrastructure/Persistence/MongoDB/Migrations',
            $paths
        );
        $this->assertContains(
            '/app/tests/Framework/Wiring/Data/Context1/Infrastructure/Persistence/MySQL/Migrations',
            $paths
        );
        $this->assertContains(
            '/app/tests/Framework/Wiring/Data/Context2/Infrastructure/Persistence/MongoDB/Migrations',
            $paths
        );
        $this->assertContains(
            '/app/tests/Framework/Wiring/Data/Context2/Infrastructure/Persistence/MySQL/Migrations',
            $paths
        );
    }

    public function test_commands_files_are_loaded(): void
    {
        /** @psalm-suppress PossiblyInvalidMethodCall */
        $this->artisan('my:command1')->expectsOutput('Command 1')->assertExitCode(0);

        /** @psalm-suppress PossiblyInvalidMethodCall */
        $this->artisan('my:command2')->expectsOutput('Command 2')->assertExitCode(0);
    }

    public function test_commands_are_scheduled(): void
    {
        /** @psalm-var Schedule $schedule */
        $schedule = $this->app?->make(Schedule::class);

        /** @psalm-var array<class-string, string> $events */
        $events = reduce(function (array $acc, Event $event) {
            if ($event->description !== null) {
                $acc[$event->description] = $event->expression;
            }
            return $acc;
        }, $schedule->events(), []);

        $this->assertArrayHasKey(MyCommand::class, $events);
        $this->assertEquals('* * * * *', $events[MyCommand::class]);

        $this->assertArrayHasKey(MyCommand2::class, $events);
        $this->assertEquals('0 * * * *', $events[MyCommand2::class]);
    }

    public function test_interface_is_bound(): void
    {
        $this->assertInstanceOf(Test1Repository::class, $this->app?->make(Test1Interface::class));
        $this->assertInstanceOf(Test2Repository::class, $this->app?->make(Test2Interface::class));
    }

    public function test_interface_is_not_bound_when_extends(): void
    {
        $this->assertNotInstanceOf(Test3Repository::class, $this->app?->make(Test2Interface::class));
    }

    public function test_interface_is_not_bound(): void
    {
        $this->expectException(\Illuminate\Contracts\Container\BindingResolutionException::class);
        $this->app?->make(Test3Interface::class);
    }

    public function getPackageProviders($app): array
    {
        return [ValidBusServiceProvider::class, TestWiringServiceProvider::class];
    }
}
