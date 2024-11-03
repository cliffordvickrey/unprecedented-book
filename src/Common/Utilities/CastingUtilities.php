<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Utilities;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use Webmozart\Assert\Assert;

class CastingUtilities
{
    /**
     * @return array<array-key, mixed>
     */
    public static function toArray(mixed $val): array
    {
        if ($val instanceof \Traversable) {
            return iterator_to_array($val);
        } elseif (\is_object($val)) {
            return (array) $val;
        } elseif (!\is_array($val)) {
            return [];
        }

        return $val;
    }

    public static function toDateTime(mixed $val): ?\DateTimeImmutable
    {
        $dt = self::buildDateTime($val);

        return $dt?->setTime(0, 0);
    }

    private static function buildDateTime(mixed $val): ?\DateTimeImmutable
    {
        if ($val instanceof \DateTimeImmutable) {
            return $val;
        }

        if ($val instanceof \DateTime) {
            return \DateTimeImmutable::createFromMutable($val);
        }

        if ($val instanceof \DateTimeInterface) {
            return \DateTimeImmutable::createFromInterface($val);
        }

        $str = self::toString($val);

        if (null === $str) {
            return null;
        }

        $dt = false;

        // FEC format (12312024)
        $strLen = \strlen($str);
        if (8 === $strLen) {
            $dt = \DateTimeImmutable::createFromFormat('mdY', $str);
        } elseif ($strLen > 10) { // probably an Internet date
            $str = substr($str, 0, 10);
        }

        // my preferred format (2024-12-31)
        if (false === $dt) {
            $dt = \DateTimeImmutable::createFromFormat('Y-m-d', $str);
        }

        // Hail, Mary!
        if (false === $dt) {
            try {
                $dt = new \DateTimeImmutable($str);
            } catch (\DateMalformedStringException) {
                return null;
            }
        }

        return $dt;
    }

    /**
     * @return non-empty-string|null
     */
    public static function toString(mixed $val): ?string
    {
        if (\is_scalar($val) || ($val instanceof \Stringable)) {
            $str = (string) $val;
        } else {
            $str = '';
        }

        if ('' === $str) {
            return null;
        }

        return $str;
    }

    public static function toInt(mixed $val): ?int
    {
        if (is_numeric($val)) {
            return (int) $val;
        }

        return null;
    }

    public static function toFloat(mixed $val): ?float
    {
        if (is_numeric($val)) {
            return (float) $val;
        }

        return null;
    }

    /**
     * @param class-string<TEnum> $classStr
     *
     * @phpstan-return TEnum
     *
     * @template TEnum of \BackedEnum
     */
    public static function toEnum(mixed $value, string $classStr): ?\BackedEnum
    {
        if (\is_object($value) && is_a($value, $classStr)) {
            return $value;
        }

        $value = self::toString($value);

        if (null === $value) {
            return null;
        }

        return $classStr::tryFrom($value) ?? $classStr::tryFrom(strtoupper($value));
    }

    /**
     * @param class-string<TEntity> $classStr
     *
     * @phpstan-return TEntity
     *
     * @template TEntity of Entity
     */
    public static function toEntity(mixed $value, string $classStr): Entity
    {
        return $classStr::create($value);
    }

    public static function toNumeric(mixed $val): float|int|null
    {
        if (!\is_scalar($val)) {
            $val = self::toString($val);
        }

        if (!is_numeric($val)) {
            return null;
        }

        if (\is_int($val)) {
            return $val;
        }

        $val = (float) $val;

        if (MathUtilities::isWholeNumber($val)) {
            return (int) $val;
        }

        return $val;
    }

    /**
     * @return numeric-string
     */
    public static function toNumericString(mixed $value): string
    {
        $numericValue = self::toNumeric($value);

        if (null === $numericValue) {
            return '0';
        }

        $strVal = (string) $numericValue;

        if (\is_int($numericValue)) {
            return $strVal;
        }

        if (!str_contains($strVal, 'E')) {
            return $strVal;
        }

        $strVal = preg_replace('/^1\./', '0.', (string) ($numericValue + 1.0));
        Assert::string($strVal);
        Assert::numeric($strVal);

        return $strVal;
    }
}
