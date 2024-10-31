<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Utilities;

use BackedEnum;

class ExtractionUtilities
{
    /**
     * @param array<array-key, mixed> $haystack
     * @param class-string<TEnum>     $classStr
     *
     * @phpstan-return TEnum
     *
     * @template TEnum of BackedEnum
     */
    public static function extractEnum(string|int $needle, array $haystack, string $classStr): ?\BackedEnum
    {
        return CastingUtilities::toEnum($haystack[$needle] ?? null, $classStr);
    }

    /**
     * @param array<array-key, mixed> $haystack
     */
    public static function extractFloat(string|int $needle, array $haystack): ?float
    {
        return CastingUtilities::toFloat($haystack[$needle] ?? null);
    }

    /**
     * @param array<array-key, mixed> $haystack
     */
    public static function extractInt(string|int $needle, array $haystack): ?int
    {
        return CastingUtilities::toInt($haystack[$needle] ?? null);
    }

    /**
     * @param array<array-key, mixed> $haystack
     *
     * @return non-empty-string|null
     */
    public static function extractString(string|int $needle, array $haystack): ?string
    {
        return CastingUtilities::toString($haystack[$needle] ?? null);
    }
}
