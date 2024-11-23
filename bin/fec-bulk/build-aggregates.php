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
use CliffordVickrey\Book2024\Common\Entity\ValueObject\Jurisdiction;
use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeDesignation;
use CliffordVickrey\Book2024\Common\Exception\BookOutOfBoundsException;
use CliffordVickrey\Book2024\Common\Repository\CandidateAggregateRepository;
use CliffordVickrey\Book2024\Common\Repository\CommitteeAggregateRepository;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;
use Webmozart\Assert\Assert;

ini_set('memory_limit', '-1');

require_once __DIR__.'/../../vendor/autoload.php';
chdir(__DIR__);

call_user_func(function () {
    // region nominees
    $reader = new CsvReader(__DIR__.'/../../data/csv/nominees.csv');

    $reader->next();

    $democraticNominees = [];
    $republicanNominees = [];

    while ($reader->valid()) {
        [$year, $jurisdiction, $democraticSlug, $republicanSlug] = $reader->current();

        Assert::numeric($year);
        Assert::string($jurisdiction);

        $key = sprintf('%d%s', $year, $jurisdiction);

        if (is_string($democraticSlug) && '' !== $democraticSlug) {
            if (!isset($democraticNominees[$democraticSlug])) {
                $democraticNominees[$democraticSlug] = [];
            }

            $democraticNominees[$democraticSlug][$key] = true;
        }

        if (is_string($republicanSlug) && '' !== $republicanSlug) {
            if (!isset($republicanNominees[$republicanSlug])) {
                $republicanNominees[$republicanSlug] = [];
            }

            $republicanNominees[$republicanSlug][$key] = true;
        }

        $reader->next();
    }

    // region candidate aggregates
    $reader = new CsvReader(__DIR__.'/../../data/csv/cn.csv');

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
            $candidateAggregate->democraticNominations = $democraticNominees[$slug] ?? [];
            $candidateAggregate->republicanNominations = $republicanNominees[$slug] ?? [];
            $candidateAggregates[$slug] = $candidateAggregate;
        }

        $info = Candidate::__set_state($rowWithHeaders);
        $candidateAggregates[$slug]->info[] = $info;

        $reader->next();
    }

    $reader->close();

    $candidateAggregateRepository = new CandidateAggregateRepository();
    $candidateAggregateRepository->deleteAll();

    array_walk(
        $candidateAggregates,
        static fn (CandidateAggregate $aggregate) => $candidateAggregateRepository->saveAggregate($aggregate)
    );

    // region committees
    $reader = new CsvReader(__DIR__.'/../../data/csv/cm.csv');

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

        $committeeName = trim(strtoupper((string) $committee->CMTE_NAME));

        if ('' !== $committeeName) {
            $committeeAggregate->name = $committeeName;
        }

        if ('' === $committeeAggregate->name) {
            $committeeAggregate->name = 'none';
        }

        $committeeAggregate->infoByYear[$committee->file_id] = $committee;

        $reader->next();
    }

    $reader->close();

    // endregion

    // region committee totals
    $reader = new CsvReader(__DIR__.'/../../data/csv/committee_summary.csv');

    $headers = array_map(strval(...), array_map(CastingUtilities::toString(...), $reader->current()));

    $reader->next();

    while ($reader->valid()) {
        $rowWithHeaders = array_combine($headers, $reader->current());

        $committeeSummary = CommitteeSummary::__set_state($rowWithHeaders);

        $totals = new CommitteeTotals();
        $totals->candidateContributions = (float) $committeeSummary->CAND_CNTB;
        $totals->itemizedReceipts = (float) $committeeSummary->INDV_ITEM_CONTB;
        $totals->unItemizedReceipts = (float) $committeeSummary->INDV_UNITEM_CONTB;
        $totals->individualReceipts = (float) $committeeSummary->INDV_CONTB;
        $totals->receipts = (float) $committeeSummary->TTL_RECEIPTS;

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
    $reader = new CsvReader(__DIR__.'/../../data/csv/ccl.csv');

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
    $reader = new CsvReader(__DIR__.'/../../data/csv/candidate_leadership_pac_linkage.csv');

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
        $lastInfo = $committeeAggregate->getLastCommittee();

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
            // prefer principal campaign committees
            $CCLs = $committeeAggregate->ccl;

            $CCLs = array_values(array_filter(
                $CCLs,
                static fn (CandidateCommitteeLinkage $ccl) => CommitteeDesignation::P === $ccl->CMTE_DSGN
            )) ?: $CCLs;

            $mostActiveYear = $committeeAggregate->getMostActiveYear();

            if (null !== $mostActiveYear) {
                $CCLs = array_values(array_filter(
                    $CCLs,
                    static fn (CandidateCommitteeLinkage $ccl) => $ccl->file_id === $mostActiveYear
                )) ?: $CCLs;
            }

            $lastCcl = $CCLs[array_key_last($CCLs)];

            try {
                $candidateAggregate = $candidateAggregateRepository->getByCandidateId($lastCcl->CAND_ID);
                $candidateInfo = $candidateAggregate->getInfo($lastCcl->file_id, $lastCcl->CAND_ID)
                    ?? throw new BookOutOfBoundsException();
                $committeeProperties->candidateSlug = $candidateAggregate->slug;
                $committeeProperties->setCandidate($candidateInfo);
            } catch (BookOutOfBoundsException) {
                printf('[CCL] Candidate with ID %s not found%s', $lastCcl->CAND_ID, \PHP_EOL);
            }
        }

        if (!empty($committeeAggregate->leadershipPacLinkage)) {
            $leaderships = $committeeAggregate->leadershipPacLinkage;
            $mostActiveYear = $committeeAggregate->getMostActiveYear();

            if (null !== $mostActiveYear) {
                $leaderships = array_values(array_filter(
                    $leaderships,
                    static fn (LeadershipPacLinkage $leadership) => $leadership->file_id === $mostActiveYear
                )) ?: $leaderships;
            }

            $lastLeadership = $leaderships[array_key_last($leaderships)];

            try {
                $candidateAggregate = $candidateAggregateRepository->getByCandidateId($lastLeadership->CAND_ID);
                $candidateInfo = $candidateAggregate->getInfo($lastLeadership->file_id, $lastLeadership->CAND_ID)
                    ?? throw new BookOutOfBoundsException();
                $committeeProperties->candidateSlug = $candidateAggregate->slug;
                $committeeProperties->isLeadership = true;
                $committeeProperties->setCandidate($candidateInfo);
            } catch (BookOutOfBoundsException) {
                printf('[leadership] Candidate with ID %s not found%s', $lastLeadership->CAND_ID, \PHP_EOL);
            }
        }

        $parsedCommittees[$committeeAggregate->id] = $committeeProperties;
    }

    // endregion

    // region generate committee slugs

    /** @var array<string, list<string>> $slugCandidates */
    $slugCandidates = array_reduce($parsedCommittees, static function (array $carry, CommitteeProperties $props) {
        $slug = $props->getSlug();

        /** @var array<string, list<string>> $carry */
        if (!isset($carry[$slug])) {
            $carry[$slug] = [$props->id];

            return $carry;
        }

        $carry[$slug][] = $props->id;

        return $carry;
    }, []);

    $disambiguatedSlugs = array_filter($slugCandidates, static fn ($group) => 1 === count($group));
    $slugsThatNeedDisambiguation = array_diff_key($slugCandidates, $disambiguatedSlugs);

    /** @var array<string, list<string>> $similarSlugGroups */
    $similarSlugGroups = [];

    foreach ($slugsThatNeedDisambiguation as $committeeIds) {
        foreach ($committeeIds as $committeeId) {
            $disambiguationCandidatesA = [];

            $committeeIdsToTest = array_filter(
                $committeeIds,
                static fn ($committeeIdToTest) => $committeeIdToTest !== $committeeId
            );

            $a = $parsedCommittees[$committeeId];

            foreach ($committeeIdsToTest as $committeeIdToTest) {
                $b = $parsedCommittees[$committeeIdToTest];

                [$slugA] = $a->performQuickAndDirtyDisambiguation($b);

                $disambiguationCandidatesA[] = $slugA;
            }

            usort(
                $disambiguationCandidatesA,
                static fn ($a, $b) => (substr_count($a, '-') <=> substr_count($b, '-')) ?: (strlen($a) <=> strlen($b))
            );

            $disambiguatedSlug = array_pop($disambiguationCandidatesA);

            if (!isset($similarSlugGroups[$disambiguatedSlug])) {
                $similarSlugGroups[$disambiguatedSlug] = [$committeeId];
                continue;
            }

            $similarSlugGroups[$disambiguatedSlug][] = $committeeId;
        }
    }

    foreach ($similarSlugGroups as $slug => $committeeIds) {
        $disambiguatedSlugsInGroup = [];

        if (count($committeeIds) < 2 || !str_contains($slug, '-')) {
            $disambiguatedSlugsInGroup[$slug] = $committeeIds;
        } else {
            foreach ($committeeIds as $committeeId) {
                $a = $parsedCommittees[$committeeId];

                $committeeIdsToTest = array_filter(
                    $committeeIds,
                    static fn ($committeeIdToTest) => $committeeIdToTest !== $committeeId
                );

                $diffMemo = [
                    CommitteeProperties::PART_DESIGNATION => null,
                    CommitteeProperties::PART_STATE => null,
                    CommitteeProperties::PART_DISTRICT => null,
                    CommitteeProperties::PART_YEAR => null,
                ];

                foreach ($committeeIdsToTest as $committeeIdToTest) {
                    $b = $parsedCommittees[$committeeIdToTest];
                    $diff = $a->diff($b);

                    foreach ($diff as $diffKey => $diffValue) {
                        if (!array_key_exists($diffKey, $diffMemo)) {
                            continue;
                        }

                        $diffMemo[$diffKey] = $diffValue;
                    }
                }

                $trailing = implode('-', array_values(array_filter($diffMemo, static fn ($val) => null !== $val)));

                if ('' !== $trailing) {
                    $trailing = "-$trailing";
                }

                $disambiguatedSlug = $slug.$trailing;

                if (!isset($disambiguatedSlugsInGroup[$disambiguatedSlug])) {
                    $disambiguatedSlugsInGroup[$disambiguatedSlug] = [$committeeId];
                    continue;
                }

                $disambiguatedSlugsInGroup[$disambiguatedSlug][] = $committeeId;
            }
        }

        foreach ($disambiguatedSlugsInGroup as $disambiguatedSlug => $disambiguatedCommitteeIds) {
            foreach ($disambiguatedCommitteeIds as $disambiguatedCommitteeId) {
                if (!isset($disambiguatedSlugs[$disambiguatedSlug])) {
                    $disambiguatedSlugs[$disambiguatedSlug] = [$disambiguatedCommitteeId];
                } else {
                    $counter = 1;

                    // functions and recursion are for WIMPS
                    do {
                        $trulyUniqueSlug = sprintf('%s__%d', $disambiguatedSlug, ++$counter);
                    } while (isset($disambiguatedSlugs[$trulyUniqueSlug]));

                    $disambiguatedSlugs[$trulyUniqueSlug] = [$disambiguatedCommitteeId];
                }
            }
        }
    }

    $committeeSlugs = array_flip(array_map(
        static fn (array $committees) => $committees[array_key_first($committees)],
        $disambiguatedSlugs
    ));

    array_walk(
        $committeeAggregates,
        static fn (CommitteeAggregate $aggregate) => $aggregate->slug = $committeeSlugs[$aggregate->id]
    );

    // endregion

    // region save committees

    $committeeAggregateRepository = new CommitteeAggregateRepository();
    $committeeAggregateRepository->deleteAll();

    array_walk(
        $committeeAggregates,
        static fn (CommitteeAggregate $aggregate) => $committeeAggregateRepository->saveAggregate($aggregate)
    );

    // trigger mapping of committee IDs to slugs
    $committeeAggregateRepository->hasCommitteeId('[null]');
    $committeeAggregateRepository->getByCommitteeName('[null]');
    $candidateAggregateRepository->getByYearAndJurisdiction(2012, new Jurisdiction('US'));

    // endregion
});
