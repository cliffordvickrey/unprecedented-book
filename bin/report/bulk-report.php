#!/usr/bin/php
<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\Common\Csv\CsvReader;
use CliffordVickrey\Book2024\Common\Csv\CsvWriter;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\ItemizedIndividualReceipt;
use CliffordVickrey\Book2024\Common\Utilities\FileUtilities;
use CliffordVickrey\Book2024\Common\Utilities\MathUtilities;

require_once __DIR__.'/../../vendor/autoload.php';

call_user_func(function (int $cycle, string $committeeId) {
    $inFile = FileUtilities::getAbsoluteCanonicalPath(sprintf(
        '%s/../../fec/bulk/itcont%d.txt',
        __DIR__,
        $cycle - 2000
    ));

    $reader = new CsvReader($inFile, '|');

    $itemizedHeaders = ItemizedIndividualReceipt::headers();
    array_shift($itemizedHeaders);

    $totalsByType = [];

    foreach ($reader as $row) {
        $itemizedReceipt = ItemizedIndividualReceipt::__set_state(array_combine($itemizedHeaders, $row));

        if ($itemizedReceipt->CMTE_ID !== $committeeId) {
            continue;
        }

        $type = $itemizedReceipt->TRANSACTION_TP?->value;

        if (null === $type) {
            continue;
        }

        if (!isset($totalsByType[$type])) {
            $totalsByType[$type] = 0.0;
        }

        $totalsByType[$type] = MathUtilities::add($totalsByType[$type], $itemizedReceipt->TRANSACTION_AMT);
    }

    $reader->close();

    asort($totalsByType, \SORT_NUMERIC);

    $outFile = sprintf(__DIR__.'/../../data/etc/%s-%d.csv', $committeeId, $cycle);

    $writer = new CsvWriter($outFile);

    foreach ($totalsByType as $type => $total) {
        $writer->write([$type, $total]);
    }

    $writer->close();
}, 2024, 'C00828541');
