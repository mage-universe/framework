<?php declare(strict_types=1);

namespace Tests\Framework\Bus\Data\ValidCase\Context\Application;

use Mage\Framework\Bus\Contracts\Event\EventHandler;
use Tests\Framework\Bus\Data\ValidCase\Context\Domain\DomainEvent;

class DomainEventEventHandler implements EventHandler
{
    public function __invoke(DomainEvent $event) {}
}
