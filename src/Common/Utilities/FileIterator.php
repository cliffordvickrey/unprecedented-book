<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Utilities;

/**
 * @extends \RecursiveIteratorIterator<\RecursiveDirectoryIterator>
 */
final class FileIterator extends \RecursiveIteratorIterator
{
    public function __construct(string $path)
    {
        $absoluteCanonicalDirname = FileUtilities::getAbsoluteCanonicalDirname($path);
        parent::__construct(new \RecursiveDirectoryIterator($absoluteCanonicalDirname));
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
