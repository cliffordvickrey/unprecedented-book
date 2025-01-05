<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\Common\Csv\ChunkedCsvWriter;
use CliffordVickrey\Book2024\Common\Csv\CsvReader;
use CliffordVickrey\Book2024\Common\Entity\Combined\Donor;
use CliffordVickrey\Book2024\Common\Service\ReceiptReadingService;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;
use CliffordVickrey\Book2024\Common\Utilities\FileIterator;
use CliffordVickrey\Book2024\Common\Utilities\FileUtilities;
use CliffordVickrey\Book2024\Common\Utilities\MathUtilities;
use Webmozart\Assert\Assert;

ini_set('memory_limit', '-1');

chdir(__DIR__);
require_once __DIR__.'/../../vendor/autoload.php';

call_user_func(function (int $chunkSize = 1000) {
    $reader = new ReceiptReadingService();
    $writer = new ChunkedCsvWriter();

    // read donors
    $donorsReader = new CsvReader(__DIR__.'/../../data/csv/donor-ids.csv');
    $headers = array_map(\strval(...), array_map(CastingUtilities::toString(...), $donorsReader->current()));
    $donorsReader->next();

    /** @var array<string, int> $minDonorIdByState */
    $minDonorIdByState = [];

    while ($donorsReader->valid()) {
        $row = array_combine($headers, $donorsReader->current());
        $donor = Donor::__set_state($row);

        Assert::greaterThan($donor->id, 0);

        $minDonorIdByState[$donor->state] = min($minDonorIdByState[$donor->state] ?? $donor->id, $donor->id);

        $donorsReader->next();
    }

    $donorsReader->close();

    $path = __DIR__.'/../../data/_receipt-chunks';
    FileUtilities::unlink($path, recursive: true);

    $files = FileIterator::getFilenames(__DIR__.'/../../data/receipts', 'csv');

    $slugs = array_map(static fn (string $file) => basename($file, '.csv'), $files);

    array_shift($headers);

    $filenameMemo = [];

    foreach ($slugs as $slug) {
        printf('Chunking donor IDs for %s...%s', $slug, \PHP_EOL);

        $receipts = $reader->readByCommitteeSlug($slug);

        foreach ($receipts as $receipt) {
            $chunkId = MathUtilities::chunkId(
                $receipt->donor_id - ($minDonorIdByState[$receipt->state] - 1),
                $chunkSize
            );

            $filename = sprintf('%s/%s/chunk%05d.csv', $path, $receipt->state, $chunkId);

            if (!isset($filenameMemo[$filename])) {
                $writer->push($filename, $headers);
                $filenameMemo[$filename] = true;
            }

            $writer->push($filename, $receipt->toArray(true));
        }
    }

    $writer->flush();
});
