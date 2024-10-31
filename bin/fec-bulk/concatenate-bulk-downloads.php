#!/usr/bin/php
<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\Common\Csv\CsvReader;
use CliffordVickrey\Book2024\Common\Csv\CsvWriter;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\Candidate;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\CandidateCommitteeLinkage;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\Committee;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\CommitteeSummary;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\LeadershipPacLinkage;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;
use CliffordVickrey\Book2024\Common\Utilities\FileUtilities;
use CliffordVickrey\Book2024\Common\Utilities\StringUtilities;
use Webmozart\Assert\Assert;

require_once __DIR__.'/../../vendor/autoload.php';
chdir(__DIR__);

call_user_func(function () {
    // first, get the candidate slugs
    $reader = new CsvReader(__DIR__.'/../../data/csv/cn/cn-slugs.csv');

    $reader->next();

    /** @var array<string, int> $candidateIds */
    $candidateIds = [];
    /** @var array<string, int> $candidateIdsBySlug */
    $candidateIdsBySlug = [];
    /** @var array<string, string> $candidateSlugs */
    $candidateSlugs = [];
    /** @var array<string, string> $candidateNames */
    $candidateNames = [];
    /** @var array<string, string> $candidateNamesBySlug */
    $candidateNamesBySlug = [];
    $candidateId = 0;

    while ($reader->valid()) {
        [$CAND_ID, $name, $slug] = $reader->current();

        Assert::string($CAND_ID);
        Assert::string($name);
        Assert::string($slug);

        if (!isset($candidateIdsBySlug[$slug])) {
            $candidateIdsBySlug[$slug] = ++$candidateId;
        }

        if (!isset($candidateNamesBySlug[$slug])) {
            $candidateNamesBySlug[$slug] = $name;
        }

        $candidateIds[$CAND_ID] = $candidateIdsBySlug[$slug];
        $candidateNames[$CAND_ID] = $candidateNamesBySlug[$slug];
        $candidateSlugs[$CAND_ID] = $slug;

        $reader->next();
    }

    $reader->close();

    // now, let's concatenate the records from the FEC master file
    $slugs = [
        'candidate_leadership_pac_linkage' => '|',
        'ccl' => '|',
        'cm' => '|',
        'cn' => '|',
        'committee_summary' => ',',
    ];

    foreach ($slugs as $slug => $delimiter) {
        $isCsv = ',' === $delimiter;
        $extension = $isCsv ? 'csv' : 'txt';

        $files = FileUtilities::glob(__DIR__."/../../fec/bulk/$slug*.$extension");

        $outHeaders = match ($slug) {
            'candidate_leadership_pac_linkage' => LeadershipPacLinkage::headers(),
            'ccl' => CandidateCommitteeLinkage::headers(),
            'cm' => Committee::headers(),
            'cn' => Candidate::headers(),
            'committee_summary' => CommitteeSummary::headers(),
        };

        $inHeaders = array_slice($outHeaders, 1);

        if ('cn' === $slug) {
            $outHeaders = [...$outHeaders, 'name', 'slug', 'id'];
        }

        $writer = new CsvWriter(__DIR__."/../../data/csv/$slug/$slug.csv");
        $writer->write($outHeaders);

        foreach ($files as $file) {
            $absoluteCanonicalFile = realpath($file);

            Assert::string($absoluteCanonicalFile, sprintf('Could not read file %s', $file));

            echo "Reading $absoluteCanonicalFile...".\PHP_EOL;

            $fileId = FileUtilities::extractFileId($absoluteCanonicalFile);

            $reader = new CsvReader($absoluteCanonicalFile, $delimiter);

            if ($isCsv || 'candidate_leadership_pac_linkage' === $slug) {
                $inHeaders = array_map(
                    static fn (mixed $value) => strtoupper((string) CastingUtilities::toString($value)),
                    $reader->current()
                );

                $reader->next();
            }

            $headerCount = count($inHeaders);

            while ($reader->valid()) {
                $current = array_pad($reader->current(), $headerCount, '');

                $row = array_combine($inHeaders, $current);

                $obj = match ($slug) {
                    'candidate_leadership_pac_linkage' => LeadershipPacLinkage::__set_state($row),
                    'ccl' => CandidateCommitteeLinkage::__set_state($row),
                    'cm' => Committee::__set_state($row),
                    'cn' => Candidate::__set_state($row),
                    'committee_summary' => CommitteeSummary::__set_state($row),
                };
                $obj->file_id = $fileId;

                $outRow = $obj->toArray(true);
                $outRowSorted = array_merge(array_intersect_key(
                    array_merge(['file_id' => $fileId, $row]),
                    $outRow
                ), $outRow);

                if ($obj instanceof Candidate) {
                    $CAND_ID = $obj->CAND_ID;

                    $candidateName = $candidateNames[$CAND_ID]
                        ?? StringUtilities::normalizeCandidateName((string) $obj->CAND_NAME);

                    $candidateSlug = $candidateSlugs[$CAND_ID]
                        ?? StringUtilities::slugify($candidateName);

                    $outRowSorted = [
                        ...$outRowSorted,
                        $candidateName,
                        $candidateSlug,
                        $candidateIds[$CAND_ID] ?? null,
                    ];
                }

                $writer->write($outRowSorted);
                $reader->next();
            }

            $reader->close();
        }

        $writer->close();
    }
});
