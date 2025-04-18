<?php declare(strict_types=1);

namespace Tests\Framework\Bus\Command;

use Illuminate\Support\Facades\Queue;
use Mage\Framework\Bus\Bridge\Job\CommandAsync;
use Mage\Framework\Bus\Bridge\Job\EventAsync;
use Mage\Framework\Bus\Command\CommandBus;
use Mage\Framework\Bus\Event\EventBus;
use Mage\Framework\Bus\Event\EventPublisher;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use Tests\Framework\Bus\BusTestCase;
use Tests\Framework\Bus\Data\AnotherValidCase\Context\Application\AnotherDomainEventEventHandler;
use Tests\Framework\Bus\Data\AnotherValidCase\Context\Domain\AnotherDomainEvent;
use Tests\Framework\Bus\Data\ValidCase\Context\Application\DomainEventEventHandler;
use Tests\Framework\Bus\Data\ValidCase\Context\Application\ValidCommand;
use Tests\Framework\Bus\Data\ValidCase\Context\Application\ValidCommandHandler;
use Tests\Framework\Bus\Data\ValidCase\Context\Application\ValidWithEventCommand;
use Tests\Framework\Bus\Data\ValidCase\Context\Application\ValidWithSyncEventCommandHandler;
use Tests\Framework\Bus\Data\ValidCase\Context\Domain\DomainEvent;
use Tests\Framework\Bus\Data\ValidCase\Context\Domain\SyncEvent;

final class CommandBusTest extends BusTestCase
{
    #[DefineEnvironment('usesValidBus')]
    public function test_handler_got_called_by_command(): void
    {
        $spy = $this->spy(ValidCommandHandler::class);
        $bus = $this->getCommandBus();
        $bus->handle(new ValidCommand());
        $spy->shouldHaveReceived('__invoke');
    }

    #[DefineEnvironment('usesValidBus')]
    public function test_command_got_pushed_to_queue(): void
    {
        Queue::fake();
        $bus = $this->getCommandBus();
        $bus->asyncHandle(new ValidCommand());
        Queue::assertPushedOn('command', CommandAsync::class);
    }

    #[DefineEnvironment('usesValidBus')]
    public function test_async_command_got_handled(): void
    {
        $spy = $this->spy(ValidCommandHandler::class);
        $bus = $this->getCommandBus();
        $commandAsync = new CommandAsync(new ValidCommand(), 'command');
        $commandAsync->handle($bus);

        $spy->shouldHaveReceived('__invoke');
        $this->assertEquals(ValidCommand::class, $commandAsync->displayName());
    }

    #[DefineEnvironment('usesValidBus')]
    public function test_called_command_dispatch_event_to_queue(): void
    {
        Queue::fake();
        $bus = $this->getCommandBus();
        $bus->handle(new ValidWithEventCommand());
        Queue::assertPushedOn('event', EventAsync::class);
    }

    #[DefineEnvironment('usesValidBus')]
    public function test_dispatched_event_got_handled(): void
    {
        $spy = $this->spy(DomainEventEventHandler::class);
        $bus = $this->getEventBus();
        $commandAsync = new EventAsync(new DomainEvent(), 'event');
        $commandAsync->handle($bus);
        $spy->shouldHaveReceived('__invoke');
    }

    #[DefineEnvironment('usesValidBus')]
    public function test_event_got_queued_using_publisher(): void
    {
        Queue::fake();
        $bus = $this->getEventBus();
        $eventPublisher = new EventPublisher($bus);
        $eventPublisher->publish(new DomainEvent());
        Queue::assertPushedOn('event', EventAsync::class);
    }

    #[DefineEnvironment('usesValidBus')]
    public function test_multi_events_got_handled(): void
    {
        $bus = $this->getEventBus();

        $spy = $this->spy(DomainEventEventHandler::class);
        $bus->dispatch(new DomainEvent());
        $spy->shouldHaveReceived('__invoke');

        $spy = $this->spy(AnotherDomainEventEventHandler::class);
        $bus->dispatch(new AnotherDomainEvent());
        $spy->shouldHaveReceived('__invoke');
    }

    #[DefineEnvironment('usesValidBus')]
    public function test_event_got_dispatched_in_sync_way(): void
    {
        $bus = $this->getEventBus();
        $spy = $this->spy(ValidWithSyncEventCommandHandler::class);
        $bus->dispatch(new SyncEvent());
        $spy->shouldHaveReceived('__invoke');
    }

    private function getCommandBus(): CommandBus
    {
        /** @psalm-var CommandBus $commandBus */
        $commandBus = $this->app?->make(CommandBus::class);
        return $commandBus;
    }

    private function getEventBus(): EventBus
    {
        /** @psalm-var EventBus $eventBus */
        $eventBus = $this->app?->make(EventBus::class);
        return $eventBus;
    }
}
