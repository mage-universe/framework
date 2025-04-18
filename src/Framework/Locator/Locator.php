<?php declare(strict_types=1);

namespace Mage\Framework\Locator;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use function Lambdish\Phunctional\reduce;

final readonly class Locator
{
    public function __construct(private Paths $paths) {}

    public function classes(): array
    {
        return $this->filesToClasses($this->files());
    }

    public function files(): array
    {
        /** @psalm-var array<int, SplFileInfo> */
        return reduce(function (array $acc, Path $pathOptions): array {
            if (!is_dir($pathOptions->root())) {
                return [];
            }

            /** @psalm-var array<int, SplFileInfo> $files */
            $files = reduce(function (array $acc, SplFileInfo $file) use ($pathOptions): array {
                if ($file->isFile() && $this->matchFiles($pathOptions->pattern(), $file)) {
                    $acc[] = $file;
                }
                return $acc;
            }, new RecursiveIteratorIterator(new RecursiveDirectoryIterator($pathOptions->root())), []);

            return [...$acc, ...$files];
        }, $this->paths->paths(), []);
    }

    private function matchFiles(string $pattern, SplFileInfo $file): bool
    {
        if ($pattern === '') {
            return false;
        }
        return preg_match($pattern, $file->getPathname()) === 1;
    }

    private function filesToClasses(array $files): array
    {
        /** @psalm-var array<int, string> */
        return reduce(function (array $acc, SplFileInfo $file): array {
            $src = $file->openFile()->fread($file->getSize());
            if (preg_match('#(namespace)(\\s+)([A-Za-z0-9\\\\]+?)(\\s*);#', $src, $matches)) {
                $className = $matches[3] . '\\' . pathinfo($file->getPathname(), PATHINFO_FILENAME);
                if (class_exists($className)) {
                    $acc[] = $className;
                }
            }
            return $acc;
        }, $files, []);
    }
}
