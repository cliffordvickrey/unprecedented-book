<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Utilities;

use Webmozart\Assert\Assert;

class FileUtilities
{
    /**
     * @return list<string>
     */
    public static function glob(string $dir, bool $withBrace = false): array
    {
        $files = glob($dir, $withBrace ? \GLOB_BRACE : 0);
        Assert::isArray($files, \sprintf('Could not find files in %s', $dir));

        return $files;
    }

    public static function fileSize(string $filename): int
    {
        $fileSize = filesize($filename);
        Assert::integer($fileSize);

        return $fileSize;
    }

    public static function saveContents(string $filename, string $contents): void
    {
        self::ensureFileDirectory($filename);

        $msg = \sprintf('Unable to write to file: %s', $filename);

        Assert::integer(file_put_contents($filename, $contents), $msg);
    }

    public static function ensureFileDirectory(string $filename): void
    {
        $dirname = \dirname($filename);

        if (is_dir($dirname)) {
            return;
        }

        Assert::true(mkdir($dirname, recursive: true), \sprintf('Unable to create directory: %s', $dirname));
    }

    public static function unlink(string $path, bool $recursive = false): void
    {
        try {
            $absoluteCanonicalPath = self::getAbsoluteCanonicalPath($path);
        } catch (\Throwable) {
            return;
        }

        $failureMsg = \sprintf('Could not delete file or folder: %s', $absoluteCanonicalPath);

        if (is_file($absoluteCanonicalPath)) {
            Assert::true(unlink($absoluteCanonicalPath), $failureMsg);

            return;
        }

        if (!$recursive) {
            Assert::true(rmdir($absoluteCanonicalPath), $failureMsg);

            return;
        }

        $filesOrFolders = scandir($path);
        Assert::isArray($filesOrFolders, \sprintf('Could not scan folder: %s', $path));

        $actualFilesOrFolders = array_values(array_filter(
            $filesOrFolders,
            static fn ($fileOrFolder) => '.' !== $fileOrFolder && '..' !== $fileOrFolder
        ));

        $absoluteCanonicalFilesOrFolders = array_map(
            static fn ($fileOrFolder) => $absoluteCanonicalPath.\DIRECTORY_SEPARATOR.$fileOrFolder,
            $actualFilesOrFolders,
        );

        array_walk(
            $absoluteCanonicalFilesOrFolders,
            static fn ($fileOrFolder) => self::unlink($fileOrFolder, true)
        );

        Assert::true(rmdir($absoluteCanonicalPath), $failureMsg);
    }

    public static function getContents(string $filename): string
    {
        Assert::file($filename, \sprintf('File does not exist: %s', $filename));

        $contents = file_get_contents($filename);

        Assert::string($contents, \sprintf('Could not read file: %s', $filename));

        return $contents;
    }

    public static function getAbsoluteCanonicalFilename(string $filename): string
    {
        $absoluteCanonicalPath = self::getAbsoluteCanonicalPath($filename);
        Assert::file($absoluteCanonicalPath, \sprintf('%s is not a file', $absoluteCanonicalPath));

        return $absoluteCanonicalPath;
    }

    public static function getAbsoluteCanonicalDirname(string $dirname): string
    {
        $absoluteCanonicalPath = self::getAbsoluteCanonicalPath($dirname);
        Assert::directory($absoluteCanonicalPath, \sprintf('%s is not a directory', $absoluteCanonicalPath));

        return $absoluteCanonicalPath;
    }

    public static function getAbsoluteCanonicalPath(string $path): string
    {
        $absoluteCanonicalPath = realpath($path);
        Assert::string($absoluteCanonicalPath, \sprintf('Could not find path: %s', $path));

        return $absoluteCanonicalPath;
    }

    public static function extractFileId(string $filename): int
    {
        $basename = basename($filename);
        $parts = explode('.', $basename);

        if (\count($parts) > 1) {
            array_pop($parts);
        }

        $basenameSansExtension = array_pop($parts);

        Assert::string($basenameSansExtension);

        Assert::integer(
            preg_match('/\d{2}$/', $basenameSansExtension, $matches),
            \sprintf('Could not extract file ID from %s', $basenameSansExtension)
        );

        $year = CastingUtilities::toInt($matches[0] ?? null);

        // FEC records start in 1977-78 election cycle, IIRC
        $century = $year < 78 ? 2000 : 1900;

        return $century + $year;
    }
}
