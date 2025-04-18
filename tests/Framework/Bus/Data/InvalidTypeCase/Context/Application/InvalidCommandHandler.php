<?php declare(strict_types=1);

namespace Tests\Framework\Bus\Data\InvalidTypeCase\Context\Application;

use Mage\Framework\Bus\Contracts\Command\CommandHandler;

/** @psalm-api */
final class InvalidCommandHandler implements CommandHandler
{
    public function __invoke(InvalidCommand $command): void {}
}
