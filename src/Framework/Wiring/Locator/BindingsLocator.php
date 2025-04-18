<?php declare(strict_types=1);

namespace Mage\Framework\Wiring\Locator;

use Mage\Framework\Locator\Locator;
use Mage\Framework\Locator\Paths;
use function Lambdish\Phunctional\map;
use function Lambdish\Phunctional\reduce;

final class BindingsLocator
{
    private Locator $locator;

    public function __construct(
        array $bindings
    ) {
        $this->locator = new Locator(new Paths($bindings));
    }

    public function bindings(): array
    {
        /** @psalm-var array<string, string> $bindings */
        $bindings = reduce(
            /** @psalm-param array<string> $acc */
            function (array $acc, string $class): array {
                /** @psalm-var class-string $class */
                $interfaces = class_implements($class);
                $parentInterfaces = get_parent_class($class) ? class_implements(get_parent_class($class)) : [];
                $interfaces = array_diff($interfaces, $parentInterfaces);

                map(function (string $interface) use (&$acc, $class): void {
                    $acc[$interface][] = $class;
                }, $interfaces);

                return $acc;
            },
            $this->locator->classes(),
            []
        );

        /** @psalm-var array<string, string> */
        return reduce(
            /** @psalm-param array<string> $binding */
            function (array $acc, array $binding, string $key) {
                if (count($binding) === 1) {
                    $acc[$key] = $binding[0];
                }
                return $acc;
            },
            $bindings,
            []
        );
    }
}
