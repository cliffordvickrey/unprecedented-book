<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Utilities;

use CliffordVickrey\Book2024\Common\Exception\BookRuntimeException;
use Webmozart\Assert\Assert;

class DateUtilities
{
    public static function getMonthsAfterStartOfElectionCycle(\DateTimeImmutable $dt): int
    {
        $interval = self::getIntervalAfterStartOfElectionCycle($dt);

        return ($interval->y * 12) + $interval->m;
    }

    public static function getWeeksAfterStartOfElectionCycle(\DateTimeImmutable $dt): int
    {
        $interval = self::getIntervalAfterStartOfElectionCycle($dt);

        $daysElapsed = $interval->format('%a');
        Assert::numeric($daysElapsed);

        return (int) floor($daysElapsed / 7);
    }

    private static function getIntervalAfterStartOfElectionCycle(\DateTimeImmutable $dt): \DateInterval
    {
        $year = $dt->format('Y');
        Assert::numeric($year);
        $year = (int) $year;

        $startYear = (0 === $year % 2) ? ($year - 1) : $year;

        $startDate = \DateTimeImmutable::createFromFormat('Y-m-d', "$startYear-01-01") ?: null;
        $startDate = $startDate?->setTime(0, 0);
        Assert::notEmpty($startDate);

        return $startDate->diff($dt);
    }

    public static function isWithinWeek(\DateTimeImmutable $a, \DateTimeImmutable $b): bool
    {
        return self::isWithinDays($a, $b, 7);
    }

    public static function isWithinDays(\DateTimeImmutable $a, \DateTimeImmutable $b, int $days): bool
    {
        $interval = $b->diff($a);
        $dayDiff = (int) $interval->days;

        return $dayDiff <= $days && 0 == $interval->invert;
    }

    /**
     * @return list<\DateTimeImmutable>
     */
    public static function getDateRanges(mixed $start, mixed $end): array
    {
        $dateA = CastingUtilities::toDateTime($start);
        Assert::notNull($dateA);
        $dateB = CastingUtilities::toDateTime($end)?->modify('+1 day');
        Assert::notNull($dateB);

        $interval = new \DateInterval('P1D'); // 1-day interval

        try {
            $datePeriod = new \DatePeriod($dateA, $interval, $dateB);
        } catch (\DateMalformedPeriodStringException $ex) {
            throw new BookRuntimeException('Could not resolve period between two dates', previous: $ex);
        }

        $datesInRange = [];

        foreach ($datePeriod as $date) {
            $datesInRange[] = $date;
        }

        return $datesInRange;
    }
}
