<?php declare(strict_types=1);

namespace Tests\Framework\Bus\Data\ValidCase\Context\Application;

use Mage\Framework\Bus\Contracts\Command\CommandHandler;

class ValidCommandHandler implements CommandHandler
{
    public function __invoke(ValidCommand $command): void {}
}
