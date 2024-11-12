#!/usr/bin/php
<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\Common\Csv\CsvReader;
use CliffordVickrey\Book2024\Common\Csv\CsvWriter;
use CliffordVickrey\Book2024\Common\Entity\Combined\Donor;
use CliffordVickrey\Book2024\Common\Entity\Combined\Receipt;
use CliffordVickrey\Book2024\Common\Entity\FecApi\ScheduleAReceipt;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\ItemizedIndividualReceipt;
use CliffordVickrey\Book2024\Common\Entity\ValueObject\ImputedCommitteeTotals;
use CliffordVickrey\Book2024\Common\Entity\ValueObject\Jurisdiction;
use CliffordVickrey\Book2024\Common\Enum\Fec\TransactionType;
use CliffordVickrey\Book2024\Common\Enum\ReceiptSource;
use CliffordVickrey\Book2024\Common\Repository\CandidateAggregateRepository;
use CliffordVickrey\Book2024\Common\Repository\CommitteeAggregateRepository;
use CliffordVickrey\Book2024\Common\Service\ReceiptWritingService;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;
use CliffordVickrey\Book2024\Common\Utilities\FileIterator;
use CliffordVickrey\Book2024\Common\Utilities\FileUtilities;
use Webmozart\Assert\Assert;

ini_set('memory_limit', '-1');

require_once __DIR__.'/../../vendor/autoload.php';
chdir(__DIR__);

call_user_func(function () {
    // a bunch of abstractions
    $candidateAggregateRepository = new CandidateAggregateRepository();
    $committeeAggregateRepository = new CommitteeAggregateRepository();
    $receiptWriter = new ReceiptWritingService();

    // donors
    $donorsWriter = new CsvWriter(__DIR__.'/../../data/csv/_unique-donors.csv');
    $donorsWriter->write(Donor::headers());
    $donorsMemo = [];

    // collect all the API files we need to read from...
    $apiFiles = FileIterator::getFilenames(__DIR__.'/../../fec/api', 'csv');

    // ...and index them by election cycle
    $apiFilesByCycle = array_reduce($apiFiles, static function (array $carry, string $file): array {
        $basename = basename($file, '.csv');
        $dt = DateTimeImmutable::createFromFormat('Y-m-d', $basename);
        Assert::isInstanceOf($dt, DateTimeImmutable::class, sprintf('Unable to parse %s as a date', $basename));

        $strYear = $dt->format('Y');
        Assert::numeric($strYear);
        $year = (int) $strYear;

        $cycle = $year;

        if (0 !== $year % 2) {
            $cycle = $year + 1;
        }

        if (!isset($carry[$cycle])) {
            $carry[$cycle] = [$file];
        } else {
            $carry[$cycle][] = $file;
        }

        return $carry;
    }, []);

    // blank slate
    $receiptWriter->deleteReceipts();

    $cycles = [2012];

    foreach ($cycles as $cycle) {
        Assert::notEmpty(
            $apiFilesByCycle[$cycle],
            sprintf('No ActBlue or WinRed data for the %d election cycle', $cycle)
        );

        printf('Parsing %d election cycle%s', $cycle, \PHP_EOL);

        // memoize totals
        /** @var array<string, ImputedCommitteeTotals> $totalsMemo */
        $totalsMemo = [];
        $write = function (Receipt $receipt) use (&$donorsMemo, $donorsWriter, $receiptWriter, &$totalsMemo): void {
            static $id = 0;

            // write receipt
            $receipt->id = ++$id;
            $receiptWriter->write($receipt);

            // parse totals
            $totals = $totalsMemo[$receipt->committee_slug] ?? new ImputedCommitteeTotals();
            $totals->addReceipt($receipt);
            $totalsMemo[$receipt->committee_slug] = $totals;

            // parse donors
            $donorHash = $receipt->getDonorHash();

            if (isset($donorsMemo[$donorHash])) {
                return;
            }

            $donorsWriter->write($receipt->toDonor()->toArray(true));
            $donorsMemo[$donorHash] = true;
        };

        // memoize small itemized receipts
        $smallItemizedReceipts = [];
        $smallItemizedReceiptWriter = new CsvWriter(sprintf(
            '%s/../../data/etc/small-unitemized-receipts%d.csv',
            __DIR__,
            $cycle
        ));

        // weird memos writer
        $irregularMemosWriter = new CsvWriter(sprintf('%s/../../data/etc/irregular-memos%d.csv', __DIR__, $cycle));

        // read itemized receipts
        $inFile = FileUtilities::getAbsoluteCanonicalPath(sprintf(
            '%s/../../fec/bulk/itcont%d.txt',
            __DIR__,
            $cycle - 2000
        ));
        printf('Reading itemized receipts (%s)%s', $inFile, \PHP_EOL);

        $reader = new CsvReader($inFile, '|');

        $itemizedHeaders = ItemizedIndividualReceipt::headers();
        array_shift($itemizedHeaders);

        $smallItemizedReceiptWriter->write(['hash', 'ct', ...Receipt::headers()]);

        foreach ($reader as $row) {
            $itemizedReceipt = ItemizedIndividualReceipt::__set_state(array_combine($itemizedHeaders, $row));

            // no date: skip
            if (null === $itemizedReceipt->TRANSACTION_DT) {
                continue;
            }

            // no amount: skip
            $amount = (float) $itemizedReceipt->TRANSACTION_AMT;

            if ($amount <= 0.0) {
                continue;
            }

            // ensure contribution (so exclude refunds, loans, and other shenanigans)
            if (!$itemizedReceipt->TRANSACTION_TP?->isContribution()) {
                continue;
            }

            // get committee aggregate
            if (!$committeeAggregateRepository->hasCommitteeId($itemizedReceipt->CMTE_ID)) {
                printf('[warning] Unknown committee ID, "%s"%s', $itemizedReceipt->CMTE_ID, \PHP_EOL);
                continue;
            }

            $committeeAggregate = $committeeAggregateRepository->getByCommitteeId($itemizedReceipt->CMTE_ID);

            // build the receipt object
            $receipt = Receipt::fromItemizedReceipt($itemizedReceipt);
            $receipt->setCommitteeAggregate($committeeAggregate);

            // defer writing if receipt is small (may need to be merged with ActBlue/WinRed data)
            if (TransactionType::_15E === $receipt->transaction_type && $receipt->isSmall()) {
                $hash = $receipt->getReceiptHash();

                if (!isset($smallItemizedReceipts[$hash])) {
                    $smallItemizedReceipts[$hash] = 1;
                } else {
                    ++$smallItemizedReceipts[$hash];
                }

                $smallItemizedReceiptWriter->write([$hash, $smallItemizedReceipts[$hash], ...$receipt->toArray(true)]);
                continue;
            }

            // write the receipt
            $write($receipt);
        }

        $reader->close();
        $smallItemizedReceiptWriter->close();

        // memo map
        /** @var array<string, string|false> $memosToCommitteeId */
        $memosToCommitteeId = [];

        $memoFilename = sprintf('%s/../../data/etc/memos%d.csv', __DIR__, $cycle);

        if (is_file($memoFilename)) {
            $memoReader = new CsvReader($memoFilename);
            $memoReader->next();

            while ($memoReader->valid()) {
                $row = $memoReader->current();

                if (count($row) < 2) {
                    $memoReader->next();
                    continue;
                }

                [$memo, $committeeId] = $row;

                if (
                    !is_string($memo)
                    || '' === $memo
                    || !is_string($committeeId)
                    || '' === $committeeId
                ) {
                    $memoReader->next();
                    continue;
                }

                $memosToCommitteeId[$memo] = $committeeId;
                $memoReader->next();
            }
        }

        // now, read the small donations we downloaded from the API
        foreach ($apiFilesByCycle[$cycle] as $inFile) {
            printf('Reading un-itemized receipts (%s)%s', $inFile, \PHP_EOL);

            $source = ReceiptSource::AB;

            if (str_contains($inFile, 'win-red')) {
                $source = ReceiptSource::WR;
            }

            $reader = new CsvReader($inFile);

            $unItemizedHeaders = ScheduleAReceipt::headers();

            foreach ($reader as $row) {
                $unItemizedReceipt = ScheduleAReceipt::__set_state(array_combine($unItemizedHeaders, $row));

                $receipt = Receipt::fromScheduleAReceipt($unItemizedReceipt);
                $receipt->source = $source;

                $memo = trim(strtoupper($unItemizedReceipt->memo_text));

                // try, heroically, to resolve the committee ID with very limited/unorganized information
                $committeeId = $memosToCommitteeId[$memo] ?? null;

                // irresolvable committee ID
                if (false === $committeeId) {
                    continue;
                }

                $committeeAggregate = null;

                // was a candidate ID provided?
                if (
                    null === $committeeId
                    && null !== $unItemizedReceipt->candidate_id
                    && $candidateAggregateRepository->hasCandidateId($unItemizedReceipt->candidate_id)
                ) {
                    $candidate = $candidateAggregateRepository->getByCandidateId($unItemizedReceipt->candidate_id);
                    $info = $candidate->getInfo($cycle, $unItemizedReceipt->candidate_id);
                    $committeeId = $info?->CAND_PCC;
                }

                // was a committee ID provided in the memo?
                if (null === $committeeId && preg_match('/\((C\d{8})\)/i', $memo, $matches)) {
                    $committeeId = $matches[1];

                    if (!$committeeAggregateRepository->hasCommitteeId($committeeId)) {
                        $committeeId = null;
                    }
                }

                // make a heroic attempt to resolve the irregular memo into an FEC committee ID
                if (null === $committeeId) {
                    $memoText = preg_replace('/^EARMARKED\sFOR/i', '', $memo);
                    Assert::string($memoText);
                    $memoText = preg_replace('/\(.*\)$/', '', trim($memoText));
                    Assert::string($memoText);

                    $year = $receipt->transaction_date->format('Y');

                    $committeeAggregate = $committeeAggregateRepository->getByCommitteeName(
                        $memoText,
                        CastingUtilities::toInt($year),
                        true
                    );
                    $committeeId = $committeeAggregate?->id;
                }

                // possible that this receipt was earmarked *before* the nominee was decided. Try to map this receipt
                // held in escrow to the right principal campaign committee
                if (null === $committeeId && ($jurisdiction = Jurisdiction::fromMemo($memo))) {
                    $nominee = $candidateAggregateRepository->getNominee(
                        year: $cycle,
                        jurisdiction: $jurisdiction,
                        isDemocratic: ReceiptSource::AB === $source
                    );

                    $candidate = $nominee?->getInfoByYearAndJurisdiction($cycle, $jurisdiction);
                    $committeeId = $candidate?->CAND_PCC;

                    printf(
                        'Contribution in escrow for future %s nominee (%s)%s',
                        $jurisdiction,
                        $nominee?->slug,
                        \PHP_EOL
                    );
                }

                if (null !== $committeeId && !$committeeAggregateRepository->hasCommitteeId($committeeId)) {
                    printf('[warning] Unknown committee ID, "%s"%s', $committeeId, \PHP_EOL);
                    $committeeId = null;
                }

                $memosToCommitteeId[$memo] = $committeeId ?? false;

                // irresolvable committee ID
                if (null === $committeeId) {
                    printf('[warning] Unprocessable receipt memo, "%s"%s', $memo, \PHP_EOL);
                    $irregularMemosWriter->write([$memo]);
                    continue;
                }

                // set committee info
                $committeeAggregate = $committeeAggregate
                    ?? $committeeAggregateRepository->getByCommitteeId($committeeId);
                $receipt->setCommitteeAggregate($committeeAggregate);

                // check if this receipt is indeed itemized
                $hash = $receipt->getReceiptHash();
                $receipt->itemized = isset($smallItemizedReceipts[$hash]);

                if ($receipt->itemized) {
                    printf('Merging %s (%s)%s', $receipt->getReceiptHash(), $receipt->committee_slug, \PHP_EOL);

                    if (1 === $smallItemizedReceipts[$hash]) {
                        unset($smallItemizedReceipts[$hash]);
                    } else {
                        --$smallItemizedReceipts[$hash];
                    }
                }

                $write($receipt);
            }
        }

        $reader->close();
        $irregularMemosWriter->close();

        // dump unmatched itemized receipts
        printf('Saving %d unmatched small un-itemized receipts%s', array_sum($smallItemizedReceipts), \PHP_EOL);
        $smallItemizedReceiptReader = $smallItemizedReceiptWriter->toReader();
        $smallItemizedReceiptReader->next();

        $receiptHeaders = Receipt::headers();

        while ($smallItemizedReceiptReader->valid()) {
            $row = $smallItemizedReceiptReader->current();
            $hash = array_shift($row);
            Assert::string($hash);

            if (!isset($smallItemizedReceipts[$hash])) {
                // already merged
                $smallItemizedReceiptReader->next();
                continue;
            }

            $receiptCount = array_shift($row);
            Assert::numeric($receiptCount);
            $receiptCount = (int) $receiptCount;

            if ($receiptCount > $smallItemizedReceipts[$hash]) {
                // already merged, but other receipts that are similar to this need to be merged
                $smallItemizedReceiptReader->next();
                continue;
            }

            $receipt = Receipt::__set_state(array_combine($receiptHeaders, $row));

            $write($receipt);

            $smallItemizedReceiptReader->next();
        }

        $smallItemizedReceiptReader->close();

        // flush all enqueued receipts
        $receiptWriter->flush();

        // save imputed totals
        $slugs = $committeeAggregateRepository->getAllSlugs();

        array_walk($slugs, function (string $slug) use ($cycle, $committeeAggregateRepository, $totalsMemo): void {
            $aggregate = $committeeAggregateRepository->getAggregate($slug);

            $extantTotals = $aggregate->imputedCommitteeTotalsByYear[$cycle] ?? null;
            $newTotals = $totalsMemo[$slug] ?? null;

            if (null === $extantTotals && null === $newTotals) {
                return;
            } elseif (null === $newTotals) {
                unset($aggregate->imputedCommitteeTotalsByYear[$cycle]);
            } else {
                $aggregate->imputedCommitteeTotalsByYear[$cycle] = $newTotals;
            }

            ksort($aggregate->imputedCommitteeTotalsByYear, \SORT_NUMERIC);
            $committeeAggregateRepository->saveAggregate($aggregate);
        });
    }
});
