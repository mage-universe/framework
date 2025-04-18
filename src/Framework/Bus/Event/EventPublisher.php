<?php declare(strict_types=1);

namespace Mage\Framework\Bus\Event;

use Mage\Framework\Bus\Contracts\Event\Event;
use function Lambdish\Phunctional\each;

final readonly class EventPublisher implements \Mage\Framework\Bus\Contracts\Event\EventPublisher
{
    public function __construct(private EventBus $eventBus) {}

    /** @psalm-param Event|array<Event> $events */
    public function publish(array|Event $events): void
    {
        $events = !is_array($events) ? [$events] : $events;
        each(fn (Event $event) => $this->eventBus->dispatch($event), $events);
    }
}
