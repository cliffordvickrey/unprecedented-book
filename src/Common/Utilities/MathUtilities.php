<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Utilities;

/**
 * @phpstan-type Coordinates array{lat: float, lon: float}
 */
class MathUtilities
{
    private const float EARTH_RADIUS = 6371000.0;

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
    public static function round(mixed $value, int $precision = 2): float
    {
        return round((float) CastingUtilities::toFloat($value), $precision);
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

    /**
     * @return Coordinates
     */
    public static function midpoint(float $lat1, float $lon1, float $lat2, float $lon2): array
    {
        [$lat1Radians, $lon1Radians, $lat2Radians, $lon2Radians] = array_map(deg2rad(...), [
            $lat1,
            $lon1,
            $lat2,
            $lon2,
        ]);

        $deltaLonRadians = $lon2Radians - $lon1Radians;

        $x = cos($lat2Radians) * cos($deltaLonRadians);
        $y = cos($lat2Radians) * sin($deltaLonRadians);

        $midPointLatRadians = atan2(
            sin($lat1Radians) + sin($lat2Radians),
            sqrt((cos($lat1Radians) + $x) * (cos($lat1Radians) + $x) + $y * $y)
        );

        $midPointLonRadians = $lon1Radians + atan2($y, cos($lat1Radians) + $x);

        return array_map(static fn ($radians) => round(rad2deg($radians), 6), [
            'lat' => $midPointLatRadians,
            'lon' => $midPointLonRadians,
        ]);
    }

    public static function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): int
    {
        [$lat1Radians, $lon1Radians, $lat2Radians, $lon2Radians] = array_map(deg2rad(...), [
            $lat1,
            $lon1,
            $lat2,
            $lon2,
        ]);

        $deltaLatRadians = $lat2Radians - $lat1Radians;
        $deltaLonRadians = $lon2Radians - $lon1Radians;

        $angle = 2 * asin(sqrt(sin($deltaLatRadians / 2) ** 2 + cos($lat1Radians) * cos($lat2Radians)
                * sin($deltaLonRadians / 2) ** 2));

        return (int) ($angle * self::EARTH_RADIUS);
    }
}
