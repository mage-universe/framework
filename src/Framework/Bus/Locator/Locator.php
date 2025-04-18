<?php declare(strict_types=1);

namespace Mage\Framework\Bus\Locator;

use Mage\Framework\Locator\Paths;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use function Lambdish\Phunctional\reduce;
use function Lambdish\Phunctional\search;

/** @internal Locator */
abstract class Locator
{
    private \Mage\Framework\Locator\Locator $locator;

    public function __construct(
        array $paths,
        private readonly string $commandInterface,
        private readonly string $commandHandlerInterface
    ) {
        $this->locator = new \Mage\Framework\Locator\Locator(new Paths($paths));
    }

    public function mappings(): array
    {
        /** @psalm-var array<string, string|array<string>> */
        return reduce(function (array $acc, string $class): array {
            if (class_exists($class) && $this->classImplements($class, $this->commandHandlerInterface)) {
                $class = new ReflectionClass($class);
                $acc = $this->setMapping($class, $acc);
            }
            return $acc;
        }, $this->locator->classes(), []);
    }

    private function classImplements(string $class, string $implements): bool
    {
        return in_array($implements, class_implements($class));
    }

    protected function setMapping(ReflectionClass $class, array $acc): array
    {
        $acc[$this->commandType($class)->getName()] = $class->getName();
        return $acc;
    }

    protected function commandType(ReflectionClass $class): ReflectionNamedType
    {
        /** @psalm-var ReflectionParameter|null $command */
        $command = search(function (ReflectionParameter $parameter): bool {
            /** @psalm-var ReflectionNamedType|null $parameterType */
            $parameterType = $parameter->getType();
            return !is_null($parameterType) &&
                $this->classImplements($parameterType->getName(), $this->commandInterface);
        }, $class->getMethod('__invoke')->getParameters());

        if (!$command) {
            throw new ReflectionException(
                "Method argument in $class->name::__invoke() implementing $this->commandInterface not detected"
            );
        }

        /** @psalm-var ReflectionNamedType */
        return $command->getType();
    }
}
