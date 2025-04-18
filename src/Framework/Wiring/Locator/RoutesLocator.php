<?php declare(strict_types=1);

namespace Mage\Framework\Wiring\Locator;

use Mage\Framework\Locator\Locator;
use Mage\Framework\Locator\Paths;
use SplFileInfo;
use function Lambdish\Phunctional\map;

final class RoutesLocator
{
    private Locator $locator;

    public function __construct(
        array $routes
    ) {
        $this->locator = new Locator(new Paths($routes));
    }

    public function routes(): array
    {
        return map(function (SplFileInfo $file): string {
            return $file->getPathname();
        }, $this->locator->files());
    }
}
