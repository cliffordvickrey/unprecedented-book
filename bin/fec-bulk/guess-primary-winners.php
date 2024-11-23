#!/usr/bin/php
<?php

use CliffordVickrey\Book2024\Common\Csv\CsvReader;
use CliffordVickrey\Book2024\Common\Csv\CsvWriter;
use CliffordVickrey\Book2024\Common\Entity\Aggregate\CandidateAggregate;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\Candidate;
use CliffordVickrey\Book2024\Common\Entity\ValueObject\CommitteeTotals;
use CliffordVickrey\Book2024\Common\Entity\ValueObject\Jurisdiction;
use CliffordVickrey\Book2024\Common\Exception\BookOutOfBoundsException;
use CliffordVickrey\Book2024\Common\Repository\CandidateAggregateRepository;
use CliffordVickrey\Book2024\Common\Repository\CommitteeAggregateRepository;
use CliffordVickrey\Book2024\Common\Utilities\FileUtilities;
use CliffordVickrey\Book2024\Common\Utilities\JsonUtilities;
use Webmozart\Assert\Assert;

require_once __DIR__.'/../../vendor/autoload.php';

call_user_func(function () {
    $json = FileUtilities::getContents(__DIR__.'/../../data/aggregate/cn/slugs-by-year-and-jurisdiction.json');

    /** @var array<int, array<string, list<string>>> $slugsByYearAndJurisdiction */
    $slugsByYearAndJurisdiction = JsonUtilities::jsonDecode($json);

    $cycles = [2012, 2014, 2016, 2018, 2020, 2022];

    $years = [];

    for ($i = ($cycles[0] - 1); $i <= $cycles[array_key_last($cycles)]; ++$i) {
        $years[] = $i;
    }

    $repo = new CandidateAggregateRepository();

    /** @var array<int, array<string, array{democrat: ?string, republican: ?string}>> $likelyPrimaryWinners */
    $likelyPrimaryWinners = [];

    $memos = [];

    foreach ($cycles as $cycle) {
        $memos[$cycle - 1] = [];
        $memos[$cycle] = [];

        $filename = __DIR__.sprintf('/../../data/etc/memo-counts%d.csv', $cycle);

        if (!is_file($filename)) {
            continue;
        }

        $reader = new CsvReader($filename);

        $reader->next();

        while ($reader->valid()) {
            [$memo] = $reader->current();

            Assert::scalar($memo);

            $memo = (string) $memo;

            $jurisdiction = Jurisdiction::fromMemo($memo);

            if (null === $jurisdiction) {
                $reader->next();
                continue;
            }

            $strJurisdiction = (string) $jurisdiction;

            $memos[$cycle - 1][] = $strJurisdiction;
            $memos[$cycle][] = $strJurisdiction;

            $reader->next();
        }
    }

    foreach ($years as $year) {
        $slugsByJurisdiction = $slugsByYearAndJurisdiction[$year] ?? [];

        $jurisdictions = array_keys($slugsByJurisdiction);

        $likelyPrimaryWinners[$year] = [];

        foreach ($jurisdictions as $strJurisdiction) {
            printf('Parsing %s (%d)...%s', $strJurisdiction, $year, \PHP_EOL);

            $jurisdiction = Jurisdiction::fromString($strJurisdiction);

            $aggregates = $repo->getByYearAndJurisdiction($year, $jurisdiction);

            /** @var list<Candidate> $candidates */
            $candidates = array_reduce(
                $aggregates,
                static fn (array $carry, CandidateAggregate $aggregate) => [
                    ...$carry,
                    ...array_values(array_filter(
                        $aggregate->info,
                        static fn (Candidate $candidate) => $candidate->CAND_ELECTION_YR === $year
                            && (string) $candidate->getJurisdiction() === (string) $jurisdiction
                            && (
                                $candidate->CAND_PTY_AFFILIATION?->isDemocratic()
                                || $candidate->CAND_PTY_AFFILIATION?->isRepublican()
                            )
                    )),
                ],
                []
            );

            $democrats = array_values(array_filter(
                $candidates,
                static fn (Candidate $candidate) => (bool) $candidate->CAND_PTY_AFFILIATION?->isDemocratic()
            ));

            $republicans = array_values(array_filter(
                $candidates,
                static fn (Candidate $candidate) => (bool) $candidate->CAND_PTY_AFFILIATION?->isRepublican()
            ));

            if (empty($democrats) && empty($republicans)) {
                continue;
            }

            $sorter = function (Candidate $a, Candidate $b) use ($year): int {
                /** @phpstan-var CommitteeAggregateRepository $committeeRepo */
                static $committeeRepo = new CommitteeAggregateRepository();

                try {
                    $pccA = $a->CAND_PCC ? $committeeRepo->getByCommitteeId($a->CAND_PCC) : null;
                } catch (BookOutOfBoundsException) {
                    $pccA = null;
                }

                try {
                    $pccB = $b->CAND_PCC ? $committeeRepo->getByCommitteeId($b->CAND_PCC) : null;
                } catch (BookOutOfBoundsException) {
                    $pccB = null;
                }

                $totalsA = $pccA?->committeeTotalsByYear[$year] ?? new CommitteeTotals();
                $totalsB = $pccB?->committeeTotalsByYear[$year] ?? new CommitteeTotals();

                return $totalsA->receipts <=> $totalsB->receipts;
            };

            usort($democrats, $sorter);
            usort($republicans, $sorter);

            /** @var Candidate|null $democraticNominee */
            $democraticNominee = null;

            if (!empty($democrats)) {
                $democraticNominee = $democrats[array_key_last($democrats)];
            }

            /** @var Candidate|null $republicanNominee */
            $republicanNominee = null;

            if (!empty($republicans)) {
                $republicanNominee = $republicans[array_key_last($republicans)];
            }

            $winners = array_map(
                fn (?string $candidateId) => $candidateId ? $repo->getByCandidateId($candidateId)->slug : null,
                [
                    'democrat' => $democraticNominee?->CAND_ID,
                    'republican' => $republicanNominee?->CAND_ID,
                ]
            );

            $likelyPrimaryWinners[$year][$strJurisdiction] = $winners;
        }
    }

    $writer = new CsvWriter(__DIR__.'/../../data/csv/_nominees.csv');

    $writer->write(['year', 'jurisdiction', 'democratic_nominee', 'republican_nominee', 'in_api_files']);

    foreach ($likelyPrimaryWinners as $year => $jurisdictions) {
        foreach ($jurisdictions as $jurisdiction => $winners) {
            $inApiFile = isset($memos[$year]) && in_array($jurisdiction, $memos[$year]);

            $writer->write([
                $year,
                $jurisdiction,
                $winners['democrat'],
                $winners['republican'],
                $inApiFile,
            ]);
        }
    }
});
