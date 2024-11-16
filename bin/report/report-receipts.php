#!/usr/bin/php
<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\Common\Csv\CsvWriter;
use CliffordVickrey\Book2024\Common\Entity\Aggregate\CommitteeAggregate;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\Candidate;
use CliffordVickrey\Book2024\Common\Entity\ValueObject\CommitteeReceiptReport;
use CliffordVickrey\Book2024\Common\Entity\ValueObject\CommitteeTotals;
use CliffordVickrey\Book2024\Common\Entity\ValueObject\ImputedCommitteeTotals;
use CliffordVickrey\Book2024\Common\Repository\CandidateAggregateRepository;
use CliffordVickrey\Book2024\Common\Repository\CommitteeAggregateRepository;
use CliffordVickrey\Book2024\Common\Utilities\MathUtilities;
use Webmozart\Assert\Assert;

ini_set('memory_limit', '-1');

require_once __DIR__.'/../../vendor/autoload.php';
chdir(__DIR__);

call_user_func(function () {
    $committeeRepo = new CommitteeAggregateRepository();
    $slugs = $committeeRepo->getAllSlugs();

    $committees = array_map(static fn (string $slug) => $committeeRepo->getAggregate($slug), $slugs);

    /** @var list<CommitteeReceiptReport> $reports */
    $reports = array_reduce($committees, static function (array $carry, CommitteeAggregate $committee): array {
        /** @phpstan-var CandidateAggregateRepository $candidateRepo */
        static $candidateRepo = new CandidateAggregateRepository();
        /** @phpstan-var list<int> $cycles */
        static $cycles = [2012, 2014, 2016, 2018, 2020, 2022];

        $candidate = null;
        $candidateSlug = $committee->getCandidateSlug();

        if (null !== $candidateSlug) {
            try {
                $candidate = $candidateRepo->getAggregate($candidateSlug);
            } catch (Webmozart\Assert\InvalidArgumentException) {
                $candidateSlug = null;
            }
        }

        printf(sprintf('Parsing %s...%s', $committee->slug, \PHP_EOL));

        foreach ($cycles as $cycle) {
            if (
                !isset($committee->committeeTotalsByYear[$cycle])
                && !isset($committee->imputedCommitteeTotalsByYear[$cycle])
            ) {
                continue;
            }

            $totals = $committee->committeeTotalsByYear[$cycle] ?? new CommitteeTotals();
            $imputedTotals = $committee->imputedCommitteeTotalsByYear[$cycle] ?? new ImputedCommitteeTotals();

            $report = new CommitteeReceiptReport();
            $report->cycle = $cycle;
            $report->genre = $candidateSlug ? 'candidate' : 'pac';
            $report->committee_slug = $committee->slug;
            $report->committee_id = $committee->id;

            Assert::notEmpty($committee->infoByYear);
            $committeeInfo = $committee->infoByYear[$cycle]
                ?? $committee->infoByYear[array_key_first($committee->infoByYear)];
            $report->committee_name = $committeeInfo->CMTE_NAME ?: $committee->name;
            $report->committee_designation = (string) $committeeInfo->CMTE_DSGN?->getSlug();

            if (null !== $candidate) {
                $report->candidate_slug = $candidate->slug;
                $candidateInfo = $candidate->getInfo($cycle);
                Assert::isInstanceOf(
                    $candidateInfo,
                    Candidate::class,
                    sprintf('Could not resolve candidate info for %s', $candidate->slug)
                );
                $report->candidate_id = $candidateInfo->CAND_ID;
                $report->candidate_name = $candidate->name;
                $report->candidate_party = $candidateInfo->CAND_PTY_AFFILIATION?->getPartySlug() ?? 'unknown';
                $report->candidate_office = (string) $candidateInfo->CAND_OFFICE?->getSlug();
                $report->candidate_jurisdiction = (string) $candidateInfo->getJurisdiction();
            }

            // canonical
            $report->itemized_receipts = $totals->itemizedReceipts;
            $report->un_itemized_receipts = $totals->unItemizedReceipts;
            $report->total_indiv_receipts = MathUtilities::add(
                $report->itemized_receipts,
                $report->un_itemized_receipts
            );

            // imputed
            $report->imputed_itemized_receipts = $imputedTotals->sumItemized();
            $report->imputed_un_itemized_receipts = $imputedTotals->sumUnItemized();
            $report->imputed_total_indiv_receipts = $imputedTotals->sumAll();
            $report->imputed_coverage = MathUtilities::divide(
                $report->imputed_total_indiv_receipts,
                $report->total_indiv_receipts,
                precision: 4
            );

            $report->itemized_act_blue_receipts = $imputedTotals->itemizedActBlue;
            $report->un_itemized_act_blue_receipts = $imputedTotals->unItemizedActBlue;
            $report->itemized_win_red_receipts = $imputedTotals->itemizedWinRed;
            $report->un_itemized_win_red_receipts = $imputedTotals->unItemizedWinRed;
            $report->large_itemized_receipts_in_bulk_file = $imputedTotals->itemizedBulkEqualToOrGreaterTo200;
            $report->small_itemized_receipts_in_bulk_file = $imputedTotals->itemizedBulkUnder200;
            $carry[] = $report;
        }

        return $carry;
    }, []);

    usort($reports, static function (CommitteeReceiptReport $a, CommitteeReceiptReport $b): int {
        $cmp = $b->cycle <=> $a->cycle;

        if ($cmp) {
            return $cmp;
        }

        $cmp = $a->genre <=> $b->genre;

        if ($cmp) {
            return $cmp;
        }

        if ('candidate' === $a->genre) {
            $cmp = $a->candidate_party <=> $b->candidate_party;
        }

        if ($cmp) {
            return $cmp;
        }

        return $b->un_itemized_receipts <=> $a->un_itemized_receipts;
    });

    $writer = new CsvWriter(__DIR__.'/../../data/report/cm-totals.csv');
    $writer->write(CommitteeReceiptReport::headers());
    array_walk($reports, static fn (CommitteeReceiptReport $report) => $writer->write($report->toArray(true)));
    $writer->close();
});
