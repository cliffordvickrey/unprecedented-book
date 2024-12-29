#!/usr/bin/php
<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\Common\Csv\CsvReader;
use CliffordVickrey\Book2024\Common\Csv\CsvWriter;
use CliffordVickrey\Book2024\Common\Entity\Combined\Donor;
use CliffordVickrey\Book2024\Common\Service\MatchResult;
use CliffordVickrey\Book2024\Common\Service\MatchService;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;
use CliffordVickrey\Book2024\Common\Utilities\FileIterator;
use CliffordVickrey\Book2024\Common\Utilities\StringUtilities;
use Webmozart\Assert\Assert;

ini_set('memory_limit', '-1');

chdir(__DIR__);
require_once __DIR__.'/../../vendor/autoload.php';

call_user_func(function () {
    $matchWriter = new CsvWriter(__DIR__.'/../../data/csv/matches.csv');
    $matchWriter->write(['id', 'similarity_score', 'donor_a', 'donor_b']);

    /**
     * @param array<string, MatchResult> $carry
     * @param array<string, Donor>       $donorsByHash
     *
     * @return array<string, MatchResult>
     */
    $reducer = static function (array $carry, array $donorsByHash) use ($matchWriter): array {
        /** @phpstan-var int $lastGeneratedId */
        static $lastGeneratedId = 0;
        /** @phpstan-var MatchService $matchService */
        static $matchService = new MatchService();

        printf(
            'Matching %s possible donors in group%s',
            StringUtilities::numberFormat(count($donorsByHash)),
            \PHP_EOL
        );

        /** @var array<string, Donor> $donorsByHash */
        $allDonors = $donorsByHash;
        $firstGeneratedId = $lastGeneratedId;

        foreach ($allDonors as $hashA => $donorA) {
            if (!isset($donorsByHash[$hashA])) {
                continue;
            }

            $donorA->id = ++$lastGeneratedId;
            $carry[$hashA] = MatchResult::newUniqueDonor($donorA);

            unset($donorsByHash[$hashA]);

            foreach ($donorsByHash as $hashB => $donorB) {
                $result = $matchService->compare($donorA, $donorB);

                if (null === $result->id) {
                    continue;
                }

                $carry[$hashB] = $result;
                $matchWriter->write($result->toSet());
                unset($donorsByHash[$hashB]);
            }
        }

        $idsGenerated = $lastGeneratedId - $firstGeneratedId;

        printf('Done! %s unique IDs generated%s', StringUtilities::numberFormat($idsGenerated), \PHP_EOL);

        return $carry;
    };

    $sorter = static fn (Donor $a, Donor $b) => strcmp($a->address ? '0' : '1', $b->address ? '0' : '1')
        ?: strnatcmp($a->name, $b->name);

    $donorChunks = FileIterator::getFilenames(__DIR__.'/../../data/_donors');

    $uniqueDonorWriter = new CsvWriter(__DIR__.'/../../data/csv/donor-ids.csv');
    $uniqueDonorWriter->write(['hash', ...Donor::headers()]);

    array_walk($donorChunks, function (string $filename) use ($uniqueDonorWriter, $reducer, $sorter) {
        printf('%s%s', str_repeat('-', 80), \PHP_EOL);
        printf('Parsing %s...%s', $filename, \PHP_EOL);

        $donorReader = new CsvReader($filename);
        $donorHeaders = array_map(strval(...), array_map(CastingUtilities::toString(...), $donorReader->current()));
        $donorReader->next();

        /** @var array<int, array<string, Donor>> $donorsByGroup */
        $donorsByGroup = [];

        while ($donorReader->valid()) {
            $donorArr = array_combine($donorHeaders, $donorReader->current());

            $groupId = $donorArr['group_id'] ?? null;
            Assert::numeric($groupId);
            $groupId = (int) $groupId;

            if (!isset($donorsByGroup[$groupId])) {
                $donorsByGroup[$groupId] = [];
            }

            $donor = Donor::__set_state($donorArr);
            $donorsByGroup[$groupId][$donor->getDonorHash()] = $donor;

            $donorReader->next();
        }

        ksort($donorsByGroup);

        /* @var callable(Donor, Donor):int<-1, 1> $sorter */
        array_walk($donorsByGroup, fn (array &$donorsByHash) => uasort($donorsByHash, $sorter));

        /** @var array<string, MatchResult> $resultsByHash */
        $resultsByHash = array_reduce($donorsByGroup, $reducer, []);

        foreach ($resultsByHash as $hash => $result) {
            $uniqueDonorWriter->write([$hash, $result->a->toArray(true)]);
        }

        $donorReader->close();
    });

    $uniqueDonorWriter->close();
    $matchWriter->close();
});
