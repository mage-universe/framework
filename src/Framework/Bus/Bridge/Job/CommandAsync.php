<?php declare(strict_types=1);

namespace Mage\Framework\Bus\Bridge\Job;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mage\Framework\Bus\Command\CommandBus;
use Mage\Framework\Bus\Contracts\Command\Command;

final class CommandAsync implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly Command $command, string $queue)
    {
        $this->queue = $queue;
    }

    /** @psalm-api */
    public function displayName(): string
    {
        return get_class($this->command);
    }

    public function handle(CommandBus $bus): void
    {
        $bus->handle($this->command);
    }
}
