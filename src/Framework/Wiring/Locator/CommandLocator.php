<?php declare(strict_types=1);

namespace Mage\Framework\Wiring\Locator;

use Mage\Framework\Console\Command;
use Mage\Framework\Locator\Locator;
use Mage\Framework\Locator\Paths;
use function Lambdish\Phunctional\reduce;

class CommandLocator
{
    private Locator $locator;

    public function __construct(
        array $commands
    ) {
        $this->locator = new Locator(new Paths($commands));
    }

    public function commands(): array
    {
        /** @psalm-var array<string, string> */
        return reduce(function (array $acc, string $class): array {
            if (is_subclass_of($class, Command::class)) {
                $acc[] = $class;
            }
            return $acc;
        }, $this->locator->classes(), []);
    }
}
