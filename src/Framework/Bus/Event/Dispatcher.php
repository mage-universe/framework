<?php declare(strict_types=1);

namespace Mage\Framework\Bus\Event;

use Mage\Framework\Bus\Contracts\Event\Event;

interface Dispatcher
{
    public function dispatch(Event $event): void;
    public function asyncDispatch(Event $event, string $queue): void;
}
