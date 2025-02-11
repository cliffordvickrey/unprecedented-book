<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Utilities;

use Webmozart\Assert\Assert;

/**
 * @phpstan-type CompressionLevel int<0, 9>
 */
class ZipUtilities
{
    public const int DEFAULT_COMPRESSION_LEVEL = 9;

    /**
     * @param CompressionLevel $level
     */
    public static function gzCompress(string $str, int $level = self::DEFAULT_COMPRESSION_LEVEL): string
    {
        $compressed = gzcompress($str, $level);
        Assert::string($compressed);

        return $compressed;
    }

    public static function gzUnCompressFile(string $filename): string
    {
        $ptr = gzopen($filename, 'r');
        Assert::resource($ptr);

        $gz = gzread($ptr, FileUtilities::fileSize($filename));
        Assert::string($gz);
        gzclose($ptr);

        $unCompressed = gzuncompress($gz);
        Assert::string($unCompressed);

        return $unCompressed;
    }
}
