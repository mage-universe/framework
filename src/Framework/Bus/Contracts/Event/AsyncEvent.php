<?php declare(strict_types=1);

namespace Mage\Framework\Bus\Contracts\Event;

interface AsyncEvent extends Event
{
    public function queue(): string;
}
