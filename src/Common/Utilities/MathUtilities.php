<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Utilities;

class MathUtilities
{
    /**
     * @param int<0, max> $precision
     */
    public static function add(mixed $addendA, mixed $addendB, int $precision = 2): float
    {
        $sum = bcadd(
            CastingUtilities::toNumericString($addendA),
            CastingUtilities::toNumericString($addendB),
            $precision + 1
        );

        return self::round($sum, $precision);
    }

    /**
     * @param int<0, max> $precision
     */
    public static function subtract(mixed $minuend, mixed $subtrahend, int $precision = 2): float
    {
        $sum = bcsub(
            CastingUtilities::toNumericString($minuend),
            CastingUtilities::toNumericString($subtrahend),
            $precision + 1
        );

        return self::round($sum, $precision);
    }

    /**
     * @param int<0, max> $precision
     */
    public static function multiply(mixed $multiplicand, mixed $multiplier, int $precision = 2): float
    {
        $sum = bcmul(
            CastingUtilities::toNumericString($multiplicand),
            CastingUtilities::toNumericString($multiplier),
            $precision + 1
        );

        return self::round($sum, $precision);
    }

    /**
     * @param int<0, max> $precision
     */
    public static function divide(mixed $dividend, mixed $divisor, int $precision = 2): float
    {
        $fltDivisor = CastingUtilities::toFloat($divisor);

        if (0.0 === $fltDivisor) {
            return 0.0;
        }

        $sum = bcdiv(
            CastingUtilities::toNumericString($dividend),
            CastingUtilities::toNumericString($divisor),
            $precision + 1
        );

        return self::round($sum, $precision);
    }

    /**
     * @param int<0, max> $precision
     */
    public static function round(mixed $value, int $precision = 2): float
    {
        return round((float) CastingUtilities::toFloat($value), $precision);
    }

    /**
     * @param int|float|numeric-string $val
     */
    public static function isWholeNumber(int|float|string $val): bool
    {
        $val = (float) $val;

        if (floor($val) !== $val) {
            return false;
        }

        return true;
    }

    public static function chunkId(int $n, int $size): int
    {
        return (int) ceil($n / $size);
    }
}
