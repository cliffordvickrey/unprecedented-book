#!/usr/bin/php
<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\Common\Csv\ChunkedCsvWriter;
use CliffordVickrey\Book2024\Common\Csv\CsvReader;
use CliffordVickrey\Book2024\Common\Entity\Combined\Donor;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;
use CliffordVickrey\Book2024\Common\Utilities\FileUtilities;
use CliffordVickrey\Book2024\Common\Utilities\MathUtilities;
use Webmozart\Assert\Assert;

ini_set('memory_limit', '-1');

chdir(__DIR__);
require_once __DIR__.'/../../vendor/autoload.php';

call_user_func(function (int $chunkSize = 500) {
    // load groups into memory
    $groupReader = new CsvReader(__DIR__.'/../../data/csv/donor-groups.csv');

    /** @var array<string, array<string, int>> $groupIds */
    $groupIds = [];
    /** @var array<string, int> $minGroupIdByState */
    $minGroupIdByState = [];

    foreach ($groupReader as $groupRow) {
        [$state, $surname, $groupId] = $groupRow;
        Assert::string($state);
        Assert::string($surname);
        Assert::numeric($groupId);

        $groupId = (int) $groupId;

        if (!isset($groupIds[$state])) {
            $groupIds[$state] = [];
        }

        $groupIds[$state][$surname] = $groupId;

        $minGroupIdByState[$state] = min($minGroupIdByState[$state] ?? 0, $groupId);
    }

    $groupReader->close();

    // chunked donors folder
    $path = __DIR__.'/../../data/_donors';

    if (is_dir($path)) {
        FileUtilities::unlink($path);
    }

    // split donors into chunks
    $donorReader = new CsvReader(__DIR__.'/../../data/csv/_unique-donors.csv');
    $donorHeaders = array_map(strval(...), array_map(CastingUtilities::toString(...), $donorReader->current()));
    $donorReader->next();

    $outputHeaders = ['group_id', ...$donorHeaders];

    $writer = new ChunkedCsvWriter();
    $filenames = [];

    while ($donorReader->valid()) {
        $donor = Donor::__set_state(array_combine($donorHeaders, $donorReader->current()));

        $state = $donor->state;
        $surname = $donor->getNormalizedSurname();

        $groupId = $groupIds[$state][$surname] ?? null;
        Assert::integer($groupId, sprintf('Missing group for %s / %s', $state, $surname));

        $chunkId = MathUtilities::chunkId($groupId - ($minGroupIdByState[$state] - 1), $chunkSize);

        $filename = sprintf('/../../data/_donors/%s/chunk%05d.csv', $state, $chunkId);

        if (!isset($filenames[$filename])) {
            $filenames[$filename] = true;
            $writer->push($filename, $outputHeaders);
        }

        $writer->push($filename, [$groupId, ...$donor->toArray(true)]);

        $donorReader->next();
    }

    $donorReader->close();
    $writer->flush();
});
