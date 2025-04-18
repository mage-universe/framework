<?php declare(strict_types=1);

namespace Mage\Framework\Locator;

use function Lambdish\Phunctional\map;

final class Paths
{
    private array $paths = [];

    public function __construct(array $paths)
    {
        map(function (array $root): void {
            /** @psalm-var array<string, string> $root */
            $this->add($root['path'], $root['pattern']);
        }, $paths);
    }

    private function add(string $root, string $pattern): void
    {
        $this->paths[] = new Path($root, $pattern);
    }

    public function paths(): array
    {
        return $this->paths;
    }
}
