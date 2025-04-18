<?php declare(strict_types=1);

namespace Tests\Framework\Wiring\Data\Context1\Infrastructure\Console;

use Mage\Framework\Console\Command;
use Mage\Framework\Console\Schedule;

/**
 * @psalm-api
 * @psalm-suppress PropertyNotSetInConstructor
 */
class MyCommand extends Command
{
    protected $signature = 'my:command1';

    public static function schedule(Schedule $scheduler): void
    {
        $scheduler->arguments([])->everyMinute();
    }

    public function handle(): void
    {
        $this->info('Command 1');
    }
}
