<?php declare(strict_types=1);

namespace Tests\Framework\Bus\Data\ValidCase\Context\Application;

use Mage\Framework\Bus\Contracts\Command\CommandHandler;
use Mage\Framework\Bus\Contracts\Event\EventPublisher;
use Tests\Framework\Bus\Data\ValidCase\Context\Domain\DomainEvent;

/** @psalm-api */
class ValidWithEventCommandHandler implements CommandHandler
{
    public function __construct(private EventPublisher $event) {}
    public function __invoke(ValidWithEventCommand $command): void
    {
        $this->event->publish(new DomainEvent());
    }
}
