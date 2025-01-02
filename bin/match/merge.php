#!/usr/bin/php
<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\Common\Csv\CsvReader;
use CliffordVickrey\Book2024\Common\Service\ReceiptReadingService;
use CliffordVickrey\Book2024\Common\Service\ReceiptWritingService;
use CliffordVickrey\Book2024\Common\Utilities\FileIterator;
use Webmozart\Assert\Assert;

ini_set('memory_limit', '-1');

chdir(__DIR__);
require_once __DIR__.'/../../vendor/autoload.php';

call_user_func(function () {
    $donorIdReader = new CsvReader(__DIR__.'/../../data/csv/donor-ids.csv');
    $donorIdReader->next();

    $donorIds = [];

    while ($donorIdReader->valid()) {
        $row = $donorIdReader->current();

        $hash = array_shift($row);
        Assert::string($hash);

        $id = array_shift($row);
        Assert::numeric($id);
        $id = (int) $id;
        Assert::greaterThan($id, 0);

        $donorIds[$hash] = $id;

        $donorIdReader->next();
    }

    $files = FileIterator::getFilenames(__DIR__.'/../../data/_receipts', 'csv');

    $slugs = array_map(static fn (string $file) => basename($file, '.csv'), $files);

    $writer = new ReceiptWritingService();
    $writer->deleteReceipts(withDonorIds: true);

    array_walk($slugs, function (string $slug) use ($donorIds, $writer) {
        /** @phpstan-var ReceiptReadingService $reader */
        static $reader = new ReceiptReadingService();

        printf('Merging donor IDs for %s...%s', $slug, \PHP_EOL);

        $receipts = $reader->readByCommitteeSlug($slug, withDonorIds: false);

        foreach ($receipts as $receipt) {
            $id = $donorIds[$receipt->getDonorHash()] ?? null;

            try {
                Assert::integer($id);
            } catch (InvalidArgumentException) {
                throw new RuntimeException(sprintf('No ID for %s', $receipt));
            }

            $receipt->donor_id = $id;
            $writer->write($receipt);
        }
    });

    $writer->flush();
});
