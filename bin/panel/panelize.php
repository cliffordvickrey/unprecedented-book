#!/usr/bin/php
<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\Common\Csv\CsvReader;
use CliffordVickrey\Book2024\Common\Entity\Combined\Donor;
use CliffordVickrey\Book2024\Common\Entity\Combined\DonorPanel;
use CliffordVickrey\Book2024\Common\Entity\Combined\DonorPanelCollection;
use CliffordVickrey\Book2024\Common\Entity\Combined\Receipt;
use CliffordVickrey\Book2024\Common\Entity\Combined\ReceiptInPanel;
use CliffordVickrey\Book2024\Common\Repository\DonorPanelRepository;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;
use CliffordVickrey\Book2024\Common\Utilities\MathUtilities;

ini_set('memory_limit', '-1');

chdir(__DIR__);
require_once __DIR__.'/../../vendor/autoload.php';

call_user_func(function (int $chunkSize = 1000) {
    // read donors
    $donorsReader = new CsvReader(__DIR__.'/../../data/csv/donor-ids.csv');
    $headers = array_map(\strval(...), array_map(CastingUtilities::toString(...), $donorsReader->current()));
    $donorsReader->next();

    /** @var list<Donor> $donorBuffer */
    $donorBuffer = [];

    $laggedId = null;
    $laggedState = null;
    $laggedChunkId = 0;
    $counter = 0;

    while ($donorsReader->valid()) {
        $row = array_combine($headers, $donorsReader->current());
        $donor = Donor::__set_state($row);

        if ($laggedState !== $donor->state) {
            $counter = 0;
        }

        if ($laggedId !== $donor->id) {
            $donorBuffer[] = $donor;
            ++$counter;
        }

        $chunkId = MathUtilities::chunkId($counter, $chunkSize);

        if ($laggedChunkId && $laggedChunkId !== $chunkId) {
            flushDonorBuffer($donorBuffer, $laggedChunkId);
        }

        $laggedChunkId = $chunkId;
        $laggedId = $donor->id;
        $laggedState = $donor->state;

        $donorsReader->next();
    }

    flushDonorBuffer($donorBuffer, $laggedChunkId);

    $donorsReader->close();
});

/**
 * @param list<Donor> $donors
 *
 * @param-out list<Donor> $donors
 */
function flushDonorBuffer(array &$donors, int $chunkId): void
{
    /** @phpstan-var literal-string&non-falsy-string $path */
    static $path = __DIR__.'/../../data/_receipt-chunks';
    /** @phpstan-var DonorPanelRepository|null $repository */
    static $repository;

    if (null === $repository) {
        $repository = new DonorPanelRepository();
        $repository->deleteAll();
    }

    if (0 === count($donors)) {
        return;
    }

    $state = $donors[0]->state;

    /** @var array<int, DonorPanel> $panelsMemo */
    $panelsMemo = [];

    $filename = sprintf('%s/%s/chunk%05d.csv', $path, $state, $chunkId);

    printf('Writing %s...%s', $filename, \PHP_EOL);

    $reader = new CsvReader($filename);
    $headers = array_map(\strval(...), array_map(CastingUtilities::toString(...), $reader->current()));

    while ($reader->valid()) {
        $receipt = Receipt::__set_state(array_combine($headers, $reader->current()));

        if (!isset($panelsMemo[$receipt->donor_id])) {
            $panel = new DonorPanel();
            $panel->donor = $receipt->toDonor();
            $panelsMemo[$receipt->donor_id] = $panel;
        } else {
            $panel = $panelsMemo[$receipt->donor_id];
        }

        $panel->receipts[] = ReceiptInPanel::fromReceipt($receipt);

        $reader->next();
    }

    $panels = new DonorPanelCollection();
    $panels->id = $chunkId;
    $panels->state = $state;
    $panels->donorPanels = array_values($panelsMemo);
    $panels->sortDonorPanels();
    $repository->save($panels);

    $donors = [];
}
