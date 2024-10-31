<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Utilities;

use Webmozart\Assert\Assert;

/**
 * @extends \RecursiveIteratorIterator<\RecursiveDirectoryIterator>
 */
final class FileIterator extends \RecursiveIteratorIterator
{
    public function __construct(string $path)
    {
        $absoluteCanonicalPath = realpath($path);

        Assert::string($absoluteCanonicalPath, \sprintf('%s is not a valid path.', $path));
        Assert::directory($absoluteCanonicalPath);

        parent::__construct(new \RecursiveDirectoryIterator($absoluteCanonicalPath));
    }

    /**
     * @param non-empty-string|null $extension
     *
     * @return list<string>
     */
    public static function getFilenames(string $path, ?string $extension = null): array
    {
        $it = new self($path);

        $files = [];

        foreach ($it as $fileInfo) {
            if (!$fileInfo instanceof \SplFileInfo) {
                continue;
            }

            if (!$fileInfo->isFile()) {
                continue;
            }

            if (null !== $extension && $fileInfo->getExtension() !== $extension) {
                continue;
            }

            $files[] = $fileInfo->getPathname();
        }

        return $files;
    }
}
