#!/usr/bin/php
<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\Common\Csv\CsvReader;
use CliffordVickrey\Book2024\Common\Entity\Aggregate\CandidateAggregate;
use CliffordVickrey\Book2024\Common\Entity\Aggregate\CommitteeAggregate;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\Candidate;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\CandidateCommitteeLinkage;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\Committee;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\CommitteeSummary;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\LeadershipPacLinkage;
use CliffordVickrey\Book2024\Common\Entity\ValueObject\CommitteeProperties;
use CliffordVickrey\Book2024\Common\Entity\ValueObject\CommitteeTotals;
use CliffordVickrey\Book2024\Common\Exception\BookOutOfBoundsException;
use CliffordVickrey\Book2024\Common\Repository\CandidateAggregateRepository;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;
use CliffordVickrey\Book2024\Common\Utilities\FileUtilities;
use CliffordVickrey\Book2024\Common\Utilities\JsonUtilities;
use Webmozart\Assert\Assert;

ini_set('memory_limit', '-1');

require_once __DIR__.'/../../vendor/autoload.php';
chdir(__DIR__);

call_user_func(function () {
    // region candidate aggregates
    $reader = new CsvReader(__DIR__.'/../../data/csv/cn/cn.csv');

    $headers = array_map(strval(...), array_map(CastingUtilities::toString(...), $reader->current()));

    $reader->next();

    $candidateAggregates = [];

    while ($reader->valid()) {
        $rowWithHeaders = array_combine($headers, $reader->current());

        $slug = $rowWithHeaders['slug'] ?? null;
        Assert::string($slug);

        if (!isset($candidateAggregates[$slug])) {
            $name = $rowWithHeaders['name'] ?? null;
            Assert::string($name);

            $candidateAggregate = new CandidateAggregate();
            $candidateAggregate->slug = $slug;
            $candidateAggregate->name = $name;
            $candidateAggregates[$slug] = $candidateAggregate;
        }

        $info = Candidate::__set_state($rowWithHeaders);
        $candidateAggregates[$slug]->info[] = $info;

        $reader->next();
    }

    $reader->close();

    $candidateAggregateRepository = new CandidateAggregateRepository();
    array_walk(
        $candidateAggregates,
        static fn (CandidateAggregate $aggregate) => $candidateAggregateRepository->saveAggregate($aggregate)
    );

    // region committees
    $reader = new CsvReader(__DIR__.'/../../data/csv/cm/cm.csv');

    /** @var array<string, CommitteeAggregate> $committeeAggregates */
    $committeeAggregates = [];

    $headers = array_map(strval(...), array_map(CastingUtilities::toString(...), $reader->current()));

    $reader->next();

    while ($reader->valid()) {
        $rowWithHeaders = array_combine($headers, $reader->current());

        $committee = Committee::__set_state($rowWithHeaders);

        if (!isset($committeeAggregates[$committee->CMTE_ID])) {
            $committeeAggregates[$committee->CMTE_ID] = new CommitteeAggregate();
        }

        $committeeAggregate = $committeeAggregates[$committee->CMTE_ID];
        $committeeAggregate->id = $committee->CMTE_ID;
        $committeeAggregate->name = (string) $committee->CMTE_NAME;
        $committeeAggregate->infoByYear[$committee->file_id] = $committee;

        $reader->next();
    }

    $reader->close();

    // endregion

    // region committee totals
    $reader = new CsvReader(__DIR__.'/../../data/csv/committee_summary/committee_summary.csv');

    $headers = array_map(strval(...), array_map(CastingUtilities::toString(...), $reader->current()));

    $reader->next();

    while ($reader->valid()) {
        $rowWithHeaders = array_combine($headers, $reader->current());

        $committeeSummary = CommitteeSummary::__set_state($rowWithHeaders);

        $totals = new CommitteeTotals();
        $totals->itemizedReceipts = (float) $committeeSummary->INDV_ITEM_CONTB;
        $totals->unItemizedReceipts = (float) $committeeSummary->INDV_UNITEM_CONTB;

        $committeeAggregate = $committeeAggregates[$committeeSummary->CMTE_ID] ?? null;

        if (null === $committeeAggregate) {
            printf('[summary] Committee with ID %s not found%s', $committeeSummary->CMTE_ID, \PHP_EOL);
            $reader->next();
            continue;
        }

        $committeeAggregate->committeeTotalsByYear[$committeeSummary->file_id] = $totals;

        $reader->next();
    }

    $reader->close();

    // endregion

    // region CCL
    $reader = new CsvReader(__DIR__.'/../../data/csv/ccl/ccl.csv');

    $headers = array_map(strval(...), array_map(CastingUtilities::toString(...), $reader->current()));

    $reader->next();

    while ($reader->valid()) {
        $rowWithHeaders = array_combine($headers, $reader->current());

        $ccl = CandidateCommitteeLinkage::__set_state($rowWithHeaders);

        $committeeAggregate = $committeeAggregates[(string) $ccl->CMTE_ID] ?? null;

        if (null === $committeeAggregate) {
            printf('[CCL] Committee with ID %s not found%s', $ccl->CMTE_ID, \PHP_EOL);
            $reader->next();
            continue;
        }

        $committeeAggregate->ccl[] = $ccl;

        $reader->next();
    }

    $reader->close();

    // endregion

    // region candidate leadership PAC linkages
    $reader = new CsvReader(
        __DIR__
        .'/../../data/csv/candidate_leadership_pac_linkage/candidate_leadership_pac_linkage.csv'
    );

    $headers = array_map(strval(...), array_map(CastingUtilities::toString(...), $reader->current()));

    $reader->next();

    while ($reader->valid()) {
        $rowWithHeaders = array_combine($headers, $reader->current());

        $candidateLeadershipPacLinkage = LeadershipPacLinkage::__set_state($rowWithHeaders);

        $committeeAggregate = $committeeAggregates[(string) $candidateLeadershipPacLinkage->CMTE_ID] ?? null;

        if (null === $committeeAggregate) {
            printf('[leadership] Committee with ID %s not found%s', $candidateLeadershipPacLinkage->CMTE_ID, \PHP_EOL);
            $reader->next();
            continue;
        }

        $committeeAggregate->leadershipPacLinkage[] = $candidateLeadershipPacLinkage;

        $reader->next();
    }

    $reader->close();

    // endregion

    // region parse names and slugs
    $parsedCommittees = [];

    foreach ($committeeAggregates as $committeeAggregate) {
        Assert::notEmpty($committeeAggregate->infoByYear);

        $lastInfo = $committeeAggregate->infoByYear[array_key_last($committeeAggregate->infoByYear)];

        $candidateIds = $committeeAggregate->getCandidateIds();

        $committeeProperties = new CommitteeProperties();
        $committeeProperties->id = $committeeAggregate->id;
        $committeeProperties->name = $committeeAggregate->name;
        $committeeProperties->committeeDesignation = $lastInfo->CMTE_DSGN;
        $committeeProperties->candidateSlugs = array_values(array_unique(array_filter(array_map(
            static fn (string $candidateId) => $candidateAggregateRepository->hasCandidateId($candidateId)
                ? $candidateAggregateRepository->getByCandidateId($candidateId)->slug
                : null,
            $candidateIds
        ))));

        if (!empty($committeeAggregate->ccl)) {
            $lastKey = array_key_last($committeeAggregate->ccl);
            $lastCcl = $committeeAggregate->ccl[$lastKey];

            try {
                $candidateAggregate = $candidateAggregateRepository->getByCandidateId($lastCcl->CAND_ID);
                $candidateInfo = $candidateAggregate->getInfo($lastCcl->file_id, $lastCcl->CAND_ID)
                    ?? throw new BookOutOfBoundsException();
                $committeeProperties->candidateSlug = $candidateAggregate->slug;
                $committeeProperties->candidateOffice = $candidateInfo->CAND_OFFICE;
                $committeeProperties->state = $candidateInfo->CAND_ELECTION_YR;
                $committeeProperties->district = $candidateInfo->CAND_OFFICE_DISTRICT;
                $committeeProperties->year = $candidateInfo->file_id;
            } catch (BookOutOfBoundsException) {
                printf('[CCL] Candidate with ID %s not found%s', $lastCcl->CAND_ID, \PHP_EOL);
            }
        }

        if (!empty($committeeAggregate->leadershipPacLinkage)) {
            $lastKey = array_key_last($committeeAggregate->leadershipPacLinkage);
            $lastLeadership = $committeeAggregate->leadershipPacLinkage[$lastKey];

            try {
                $candidateAggregate = $candidateAggregateRepository->getByCandidateId($lastLeadership->CAND_ID);
                $candidateInfo = $candidateAggregate->getInfo($lastLeadership->file_id, $lastLeadership->CAND_ID)
                    ?? throw new BookOutOfBoundsException();
                $committeeProperties->candidateSlug = $candidateAggregate->slug;
                $committeeProperties->isLeadership = true;
                $committeeProperties->year = $candidateInfo->CAND_ELECTION_YR;
            } catch (BookOutOfBoundsException) {
                printf('[leadership] Candidate with ID %s not found%s', $lastLeadership->CAND_ID, \PHP_EOL);
            }
        }

        $parsedCommittees[$committeeAggregate->id] = $committeeProperties;
    }

    // endregion

    // region generate committee slugs
    $slugCandidates = array_reduce($parsedCommittees, static function (array $carry, CommitteeProperties $props) {
        $slug = $props->getSlug();

        if (!isset($carry[$slug])) {
            $carry[$slug] = [$props->id];

            return $carry;
        }

        $carry[$slug][] = $props->id;

        return $carry;
    }, []);

    $disambiguatedSlugs = array_filter($slugCandidates, static fn ($group) => 1 === count($group));
    $slugsThatNeedDisambiguation = array_diff_key($slugCandidates, $disambiguatedSlugs);

    foreach ($slugsThatNeedDisambiguation as $committeeIds) {
        foreach ($committeeIds as $committeeId) {
            $disambiguationCandidates = [];

            $committeeIdsToTest = array_filter(
                $committeeIds,
                static fn ($committeeIdToTest) => $committeeIdToTest !== $committeeId
            );

            $a = $parsedCommittees[$committeeId];

            foreach ($committeeIdsToTest as $committeeIdToTest) {
                $b = $parsedCommittees[$committeeIdToTest];

                [$slugA] = $a->disambiguateSlugs($b);

                $disambiguationCandidates[] = $slugA;
            }

            usort($disambiguationCandidates, static fn ($a, $b) => substr_count($a, '-') <=> substr_count($b, '-'));

            $mostSpecificSlug = array_pop($disambiguationCandidates);

            if (!isset($disambiguatedSlugs[$mostSpecificSlug])) {
                $disambiguatedSlugs[$mostSpecificSlug] = [$committeeId];
            } else {
                $counter = 1;

                // functions and recursion are for WIMPS
                do {
                    $superDisambiguatedSlug = sprintf('%s-%d', $mostSpecificSlug, ++$counter);
                } while (isset($disambiguatedSlugs[$superDisambiguatedSlug]));

                $disambiguatedSlugs[$superDisambiguatedSlug] = [$committeeId];
            }
        }
    }

    $committeeSlugs = array_flip(array_map(
        static fn (array $committees) => $committees[array_key_first($committees)],
        $disambiguatedSlugs
    ));

    FileUtilities::saveContents(__DIR__.'/committee-slugs.json', JsonUtilities::jsonEncode($committeeSlugs, true));

    // endregion
});
