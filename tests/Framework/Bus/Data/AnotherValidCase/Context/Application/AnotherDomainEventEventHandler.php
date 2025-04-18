<?php declare(strict_types=1);

namespace Tests\Framework\Bus\Data\AnotherValidCase\Context\Application;

use Mage\Framework\Bus\Contracts\Event\EventHandler;
use Tests\Framework\Bus\Data\AnotherValidCase\Context\Domain\AnotherDomainEvent;

class AnotherDomainEventEventHandler implements EventHandler
{
    public function __invoke(AnotherDomainEvent $event) {}
}
