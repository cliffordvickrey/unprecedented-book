<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\Common\Csv\CsvWriter;
use CliffordVickrey\Book2024\Common\Service\ReceiptReadingService;
use CliffordVickrey\Book2024\Common\Utilities\DateUtilities;
use CliffordVickrey\Book2024\Common\Utilities\MathUtilities;
use Webmozart\Assert\Assert;

require_once __DIR__.'/../../vendor/autoload.php';

call_user_func(function () {
    $candidates = [
        'kamala_harris' => [
            'C00703975', // PCC,
            'C00744946', // HARRIS ACTION FUND,
            'C00838912', // HARRIS VICTORY FUND,
            'C00658476', // DEMOCRATIC GRASSROOTS VICTORY FUND
            'C00010603', // DNC
        ],
        'donald_trump' => [
            'C00828541', // PCC
            'C00867937', // TRUMP 47
            'C00873893', // TRUMP NATIONAL COMMITTEE
            'C00770941', // SAVE AMERICA
            'C00580100', // MAKE AMERICA GREAT AGAIN
            'C00618371', // TRUMP MAKE AMERICA GREAT AGAIN
            'C00855114', // TRUMP BILIRAKIS VICTORY FUND
            'C00003418', // RNC
        ],
    ];

    $startDate = '2022-11-15';
    $endDate = '2024-09-30';

    $report = [];

    foreach ($candidates as $candidateSlug => $committeeIds) {
        $report[$candidateSlug] = array_reduce($committeeIds,
            static function (array $carry, string $committeeId) use ($startDate, $endDate): array {
                /** @phpstan-var ReceiptReadingService $service */
                static $service = new ReceiptReadingService();

                $receipts = $service->readByCommitteeId($committeeId, withDonorIds: false);

                foreach ($receipts as $receipt) {
                    $dt = $receipt->transaction_date->format('Y-m-d');

                    if ($dt < $startDate || $dt > $endDate) {
                        continue;
                    }

                    $row = $carry[$dt] ?? [0, 0.0];
                    Assert::isArray($row);
                    [$ct, $amt] = $row;
                    Assert::integer($ct);

                    $carry[$dt] = [++$ct, MathUtilities::add($amt, $receipt->amount)];
                }

                return $carry;
            },
            []
        );

        ksort($report[$candidateSlug]);
    }

    $outputFile = __DIR__.'/../../data/report/daily-receipts.csv';

    $writer = new CsvWriter($outputFile);

    $candidateSlugs = array_keys($candidates);

    /** @var list<string> $headers */
    $headers = array_reduce($candidateSlugs, static fn (array $carry, string $candidateSlug) => [
        ...$carry,
        ...["{$candidateSlug}_ct", "{$candidateSlug}_amt"],
    ], ['date']);

    $writer->write($headers);

    $dates = DateUtilities::getDateRanges($startDate, $endDate);

    foreach ($dates as $date) {
        $dt = $date->format('Y-m-d');

        $outputRow = [$dt];

        foreach ($candidateSlugs as $candidateSlug) {
            /** @var array{0: int, 1: float} $outputPart */
            $outputPart = $report[$candidateSlug][$dt] ?? [0, 0.0];
            $outputRow = [...$outputRow, ...$outputPart];
        }

        $writer->write($outputRow);
    }

    $writer->close();
});
