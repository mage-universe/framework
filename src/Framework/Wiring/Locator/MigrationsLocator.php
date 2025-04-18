<?php declare(strict_types=1);

namespace Mage\Framework\Wiring\Locator;

use Mage\Framework\Locator\Locator;
use Mage\Framework\Locator\Paths;
use SplFileInfo;
use function Lambdish\Phunctional\map;

final class MigrationsLocator
{
    private Locator $locator;

    public function __construct(
        array $migrations
    ) {
        $this->locator = new Locator(new Paths($migrations));
    }

    public function migrations(): array
    {
        return map(function (SplFileInfo $file): string {
            return $file->getPath();
        }, $this->locator->files());
    }
}
