<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\Common\Csv\CsvReader;
use CliffordVickrey\Book2024\Common\Csv\CsvWriter;
use CliffordVickrey\Book2024\Common\Entity\Combined\Donor;
use CliffordVickrey\Book2024\Common\Service\MatchService;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;
use CliffordVickrey\Book2024\Common\Utilities\StringUtilities;

ini_set('memory_limit', '-1');

chdir(__DIR__);
require_once __DIR__.'/../../vendor/autoload.php';

call_user_func(function () {
    $reader = new CsvReader(__DIR__.'/../../data/csv/_unique-donors.csv');
    $headers = array_map(strval(...), array_map(CastingUtilities::toString(...), $reader->current()));
    $reader->next();

    $matchService = new MatchService();

    /** @var array<string, array<string, bool>> $surnamesByState */
    $surnamesByState = [];

    $counter = 0;

    while ($reader->valid()) {
        $donor = Donor::__set_state(array_combine($headers, $reader->current()));

        ++$counter;

        if (0 === $counter % 1000000) {
            printf('Loaded %s donors%s', StringUtilities::numberFormat($counter), \PHP_EOL);
        }

        if (!isset($surnamesByState[$donor->state])) {
            $surnamesByState[$donor->state] = [];
        }

        $surname = $donor->getNormalizedSurname();

        if (!isset($surnamesByState[$donor->state][$surname])) {
            $surnamesByState[$donor->state][$surname] = true;
        }

        $reader->next();
    }

    ksort($surnamesByState);

    $reader->close();

    $counter = 0;

    $writer = new CsvWriter(__DIR__.'/../../data/csv/donor-groups.csv');

    foreach ($surnamesByState as $state => $surnamesMap) {
        $surnames = array_keys($surnamesMap);
        unset($surnamesByState[$state]);
        sort($surnames);

        foreach ($surnames as $i => $surname) {
            printf('%s%s', str_repeat('-', 80), \PHP_EOL);
            printf('ID %d: %s - %s%s', ++$counter, $state, $surname, \PHP_EOL);
            $matchedSurnames = [$surname];
            unset($surnames[$i]);

            foreach ($surnames as $ii => $surnameToMatch) {
                if (!$matchService->areSurnamesSimilar($surname, $surnameToMatch)) {
                    continue;
                }

                printf('ID %d: %s - %s%s', $counter, $state, $surnameToMatch, \PHP_EOL);
                $matchedSurnames[] = $surnameToMatch;
                unset($surnames[$ii]);
            }

            array_walk($matchedSurnames, fn ($matchedSurname) => $writer->write([
                $state,
                $matchedSurname,
                $counter,
            ]));
        }
    }

    $writer->close();
});
