<?php declare(strict_types=1);

namespace Mage\Framework\Locator;

final readonly class Path
{
    public function __construct(
        private string $root,
        private string $pattern
    ) {}

    public function root(): string
    {
        return $this->root;
    }

    public function pattern(): string
    {
        return $this->pattern;
    }
}
