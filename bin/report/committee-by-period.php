#!/usr/bin/php
<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\Common\Csv\CsvWriter;
use CliffordVickrey\Book2024\Common\Entity\Combined\Receipt;
use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeFilingFrequency;
use CliffordVickrey\Book2024\Common\Repository\CommitteeAggregateRepository;
use CliffordVickrey\Book2024\Common\Service\ReceiptReadingService;
use CliffordVickrey\Book2024\Common\Utilities\DateUtilities;
use CliffordVickrey\Book2024\Common\Utilities\MathUtilities;
use Webmozart\Assert\Assert;

chdir(__DIR__);
require_once __DIR__.'/../../vendor/autoload.php';

call_user_func(function () {
    $committeeRepo = new CommitteeAggregateRepository();
    $receiptReadingService = new ReceiptReadingService();

    $committeeIds = [
        'C00703975', // HARRIS PCC
        'C00744946', // HARRIS ACTION FUND,
        'C00838912', // HARRIS VICTORY FUND,
        'C00658476', // DEMOCRATIC GRASSROOTS VICTORY FUND
        'C00010603', // DNC SERVICES CORP
        'C00828541', // TRUMP PCC
        'C00867937', // TRUMP 47
        'C00873893', // TRUMP NATIONAL COMMITTEE
        'C00770941', // SAVE AMERICA
        'C00580100', // MAKE AMERICA GREAT AGAIN
        'C00618371', // TRUMP MAKE AMERICA GREAT AGAIN
        'C00855114', // TRUMP BILIRAKIS VICTORY FUND
        'C00003418', // RNC
    ];

    /** @var array<string, array{0: string, 1: string}> $monthlyPeriods */
    $monthlyPeriods = [
        '2023_M2' => ['2023-01-01', '2023-01-31'],
        '2023_M3' => ['2023-02-01', '2023-02-28'],
        '2023_M4' => ['2023-03-01', '2023-03-31'],
        '2023_M5' => ['2023-04-01', '2023-04-30'],
        '2023_M6' => ['2023-05-01', '2023-05-31'],
        '2023_M7' => ['2023-06-01', '2023-06-30'],
        '2023_M8' => ['2023-07-01', '2023-07-31'],
        '2023_M9' => ['2023-08-01', '2023-08-31'],
        '2023_M10' => ['2023-09-01', '2023-09-30'],
        '2023_M11' => ['2023-10-01', '2023-10-31'],
        '2023_M12' => ['2023-11-01', '2023-11-30'],
        '2023_YE' => ['2023-12-01', '2023-12-31'],
        '2024_M2' => ['2024-01-01', '2024-01-31'],
        '2024_M3' => ['2024-02-01', '2024-02-29'],
        '2024_M4' => ['2024-03-01', '2024-03-31'],
        '2024_M5' => ['2024-04-01', '2024-04-30'],
        '2024_M6' => ['2024-05-01', '2024-05-31'],
        '2024_M7' => ['2024-06-01', '2024-06-30'],
        '2024_M8' => ['2024-07-01', '2024-07-31'],
        '2024_M9' => ['2024-08-01', '2024-08-31'],
        '2024_M10' => ['2024-09-01', '2024-09-30'],
        '2024_12G' => ['2024-10-01', '2024-10-16'],
        '2024_30G' => ['2024-10-17', '2024-11-25'],
        '2024_YE' => ['2024-11-26', '2024-12-31'],
    ];

    /** @var array<string, array{0: string, 1: string}> $quarterlyPeriods */
    $quarterlyPeriods = [
        '2023_Q1' => ['2024-01-01', '2024-03-31'],
        '2023_Q2' => ['2024-04-01', '2024-06-30'],
        '2023_Q3' => ['2024-07-01', '2024-09-30'],
        '2023_YE' => ['2024-01-01', '2024-03-31'],
        '2024_Q1' => ['2024-01-01', '2024-03-31'],
        '2024_Q2' => ['2024-04-01', '2024-06-30'],
        '2024_Q3' => ['2024-07-01', '2024-09-30'],
        '2024_12G' => ['2024-10-01', '2024-10-16'],
        '2024_30G' => ['2024-10-17', '2024-11-25'],
        '2024_YE' => ['2024-11-26', '2024-12-31'],
    ];

    /**
     * @param array<string, array{0: string, 1: string}> $dates
     *
     * @return array<string, string>
     */
    $reducer = function (array $dates): array {
        $datesByPeriod = [];

        foreach ($dates as $period => $term) {
            Assert::isArray($term);
            [$startDate, $endDate] = $term;

            $dates = array_map(
                static fn (DateTimeImmutable $dt) => $dt->format('Y-m-d'),
                DateUtilities::getDateRanges($startDate, $endDate)
            );

            $datesByPeriod = array_merge($datesByPeriod, array_combine($dates, array_fill(0, count($dates), $period)));
        }

        return $datesByPeriod;
    };

    $monthlyDates = $reducer($monthlyPeriods);
    $quarterlyDates = $reducer($quarterlyPeriods);

    $report = [[
        'committee_id',
        'committee_slug',
        'year',
        'report_type',
        'coverage_start_date',
        'coverage_end_date',
        'un_itemized_amount',
        'itemized_amount',
        'total_amount',
    ]];

    foreach ($committeeIds as $committeeId) {
        /** @var array<string, float> $itemized */
        $itemized = [];
        /** @var array<string, float> $unItemized */
        $unItemized = [];

        $committee = $committeeRepo->getByCommitteeId($committeeId);

        $info = $committee->infoByYear[2024] ?? null;

        Assert::notNull($info);

        $isQuarterly = CommitteeFilingFrequency::Q === $info->CMTE_FILING_FREQ;

        $dates = $isQuarterly ? $quarterlyDates : $monthlyDates;
        $periods = $isQuarterly ? $quarterlyPeriods : $monthlyPeriods;

        $receipts = $receiptReadingService->readByCommitteeId($committeeId, false);

        foreach ($receipts as $receipt) {
            /** @var Receipt $receipt */
            $period = $dates[$receipt->transaction_date->format('Y-m-d')] ?? null;

            if (null === $period || !$receipt->transaction_type->isLine11A()) {
                continue;
            }

            if ($receipt->itemized) {
                $itemized[$period] = MathUtilities::add($itemized[$period] ?? 0.0, $receipt->amount);
                continue;
            }

            $unItemized[$period] = MathUtilities::add($unItemized[$period] ?? 0.0, $receipt->amount);
        }

        foreach ($periods as $period => $term) {
            $unItemizedAmount = $unItemized[$period] ?? 0.0;
            $itemizedAmount = $itemized[$period] ?? 0.0;
            $totalAmount = MathUtilities::add($unItemizedAmount, $itemizedAmount);

            if (0.0 === $totalAmount) {
                continue;
            }

            [$startDate, $endDate] = $term;

            $parts = explode('_', $period);

            $year = $parts[0];
            $reportType = $parts[1] ?? '';

            $report[] = [
                $committeeId,
                $committee->slug,
                $year,
                $reportType,
                $startDate,
                $endDate,
                $unItemizedAmount,
                $itemizedAmount,
                $totalAmount,
            ];
        }
    }

    $csvWriter = new CsvWriter(__DIR__.'/../../data/report/committee-by-period.csv');

    array_walk($report, static fn (array $row) => $csvWriter->write($row));

    $csvWriter->close();
});
