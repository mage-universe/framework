<?php declare(strict_types=1);

namespace Tests\Framework\Bus\Data\ValidCase\Context\Domain;

use Mage\Framework\Bus\Contracts\Event\AsyncEvent;

class DomainEvent implements AsyncEvent
{
    public function queue(): string
    {
        return 'event';
    }
}
