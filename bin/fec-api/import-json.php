#!/usr/bin/php
<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\Common\Csv\CsvReader;
use CliffordVickrey\Book2024\Common\Csv\CsvWriter;
use CliffordVickrey\Book2024\Common\Entity\FecApi\ScheduleAReceipt;
use CliffordVickrey\Book2024\Common\Utilities\FileIterator;

require_once __DIR__.'/../../vendor/autoload.php';

call_user_func(function () {
    $filenames = FileIterator::getFilenames(__DIR__.'/../../fec/api', 'txt');

    $sep = \DIRECTORY_SEPARATOR;

    $replacements = [
        "{$sep}_act-blue-schedule-a$sep" => "{$sep}act-blue-schedule-a$sep",
        "{$sep}_win-red-schedule-a$sep" => "{$sep}win-red-schedule-a$sep",
    ];

    array_walk($filenames, static function (string $filename) use ($replacements): void {
        echo sprintf('Parsing %s... ', $filename);

        $outputFilename = str_replace(
            array_keys($replacements),
            array_values($replacements),
            (string) preg_replace('/\.txt$/', '.csv', $filename)
        );

        if (is_file($outputFilename)) {
            echo sprintf('%s%s already exists. Skipping%s', \PHP_EOL, $filename, \PHP_EOL);

            return;
        }

        $reader = new CsvReader($filename, CsvReader::JSON);
        $writer = new CsvWriter($outputFilename);

        foreach ($reader as $inRow) {
            // look ma, no loops!
            $outRows = array_map(
                static fn (ScheduleAReceipt $receipt) => array_map(
                    static fn (mixed $value) => is_string($value) ? str_replace('\\', '', $value) : $value,
                    $receipt->toArray(true)
                ),
                ScheduleAReceipt::collectList($inRow)
            );

            array_walk($outRows, static fn (array $outRow) => $writer->write($outRow));
        }

        echo 'Done!'.\PHP_EOL;

        $reader->close();
        $writer->close();
    });
});
