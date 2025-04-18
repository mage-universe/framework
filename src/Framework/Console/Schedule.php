<?php declare(strict_types=1);

namespace Mage\Framework\Console;

use Illuminate\Console\Scheduling\Event;

readonly class Schedule
{
    private const int BACKTRACE_LIMIT = 2;

    public function __construct(private \Illuminate\Console\Scheduling\Schedule $schedule) {}

    public function arguments(array $arguments = []): Event
    {
        /** @psalm-var string $command */
        $command = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, self::BACKTRACE_LIMIT)[1]['class'] ?? null;
        return $this->schedule->command($command, $arguments)->description($command);
    }
}
