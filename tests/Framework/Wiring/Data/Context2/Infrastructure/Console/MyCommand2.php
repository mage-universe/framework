<?php declare(strict_types=1);

namespace Tests\Framework\Wiring\Data\Context2\Infrastructure\Console;

use Mage\Framework\Console\Command;
use Mage\Framework\Console\Schedule;

/**
 * @psalm-api
 * @psalm-suppress PropertyNotSetInConstructor
 */
class MyCommand2 extends Command
{
    protected $signature = 'my:command2';

    public static function schedule(Schedule $scheduler): void
    {
        $scheduler->arguments([])->hourly();
    }

    public function handle(): void
    {
        $this->info('Command 2');
    }
}
