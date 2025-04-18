<?php declare(strict_types=1);

namespace Tests\Framework\Bus\Data\AnotherValidCase\Context\Domain;

use Mage\Framework\Bus\Contracts\Event\AsyncEvent;

class AnotherDomainEvent implements AsyncEvent
{
    public function queue(): string
    {
        return 'event';
    }
}
