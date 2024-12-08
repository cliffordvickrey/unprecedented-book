<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Utilities;

use CliffordVickrey\Book2024\Common\Exception\BookRuntimeException;
use Webmozart\Assert\Assert;

class DateUtilities
{
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
