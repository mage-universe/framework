<?php declare(strict_types=1);

namespace Mage\Framework\Bus\Bridge\Job;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mage\Framework\Bus\Contracts\Event\Event;
use Mage\Framework\Bus\Event\EventBus;

final class EventAsync implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly Event $event, string $queue)
    {
        $this->queue = $queue;
    }

    /** @psalm-api */
    public function displayName(): string
    {
        return get_class($this->event);
    }

    public function handle(EventBus $bus): void
    {
        $bus->dispatchNow($this->event);
    }
}
