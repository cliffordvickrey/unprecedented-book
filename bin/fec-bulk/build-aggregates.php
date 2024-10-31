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

        Assert::notNull($committeeAggregate, sprintf('Committee with ID %s not found', $committeeSummary->CMTE_ID));

        $committeeAggregate->committeeTotalsByYear[$committeeSummary->file_id] = $totals;

        $reader->next();
    }

    $reader->close();

    // endregion

    // region CCL
    $reader = new CsvReader(__DIR__.'/../../data/csv/committee_summary/committee_summary.csv');

    $headers = array_map(strval(...), array_map(CastingUtilities::toString(...), $reader->current()));

    $reader->next();

    while ($reader->valid()) {
        $rowWithHeaders = array_combine($headers, $reader->current());

        $ccl = CandidateCommitteeLinkage::__set_state($rowWithHeaders);

        $committeeAggregate = $committeeAggregates[(string) $ccl->CMTE_ID] ?? null;

        Assert::notNull($committeeAggregate, sprintf('Committee with ID %s not found', $ccl->CMTE_ID));

        $committeeAggregate->cclByYear[$ccl->file_id] = $ccl;

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

        Assert::notNull(
            $committeeAggregate,
            sprintf('Committee with ID %s not found', $candidateLeadershipPacLinkage->CMTE_ID)
        );

        $committeeAggregate->leadershipPacLinkageByYear[$candidateLeadershipPacLinkage->file_id] =
            $candidateLeadershipPacLinkage;

        $reader->next();
    }

    $reader->close();

    // endregion

    // region parse names and slugs
    $parsed = [];

    foreach ($committeeAggregates as $committeeAggregate) {
        Assert::notEmpty($committeeAggregate->infoByYear);

        $lastInfo = $committeeAggregate->infoByYear[array_key_last($committeeAggregate->infoByYear)];

        $committeeProperties = new CommitteeProperties();
        $committeeProperties->committeeDesignation = $lastInfo->CMTE_DSGN;

        if (!empty($committeeAggregate->cclByYear)) {
            $lastKey = array_key_last($committeeAggregate->cclByYear);
            $lastCcl = $committeeAggregate->cclByYear[$lastKey];
            $candidateAggregate = $candidateAggregateRepository->getByCandidateId($lastCcl->CAND_ID);
            $candidateInfo = $candidateAggregate->getInfo($lastCcl->file_id, $lastCcl->CAND_ID);
            Assert::notNull($candidateInfo);

            $committeeProperties->candidateSlug = $candidateAggregate->slug;
            $committeeProperties->candidateOffice = $candidateInfo->CAND_OFFICE;
            $committeeProperties->state = $candidateInfo->CAND_OFFICE_ST;
            $committeeProperties->district = $candidateInfo->CAND_OFFICE_DISTRICT;
        }

        if (!empty($committeeAggregate->leadershipPacLinkageByYear)) {
            $lastKey = array_key_last($committeeAggregate->cclByYear);
            $lastLeadership = $committeeAggregate->leadershipPacLinkageByYear[$lastKey];
            $candidateAggregate = $candidateAggregateRepository->getByCandidateId($lastLeadership->CAND_ID);
            $candidateInfo = $candidateAggregate->getInfo($lastLeadership->file_id, $lastLeadership->CAND_ID);
            Assert::notNull($candidateInfo);

            $committeeProperties->candidateSlug = $candidateAggregate->slug;
            $committeeProperties->isLeadership = true;
        }

        $parsed[$committeeAggregate->id] = $committeeProperties;
    }

    $json = JsonUtilities::jsonEncode($parsed, true);
    FileUtilities::saveContents(__DIR__.'/parsed.json', $json);

    // endregion
});
