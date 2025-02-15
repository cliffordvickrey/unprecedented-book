#!/usr/bin/php
<?php

/**
 * Split the national ZCTA geojson file into states.
 */

declare(strict_types=1);

use CliffordVickrey\Book2024\Common\Csv\CsvReader;
use CliffordVickrey\Book2024\Common\Enum\State;
use CliffordVickrey\Book2024\Common\Utilities\FileUtilities;
use Webmozart\Assert\Assert;

ini_set('memory_limit', '-1');

chdir(__DIR__);
require_once __DIR__.'/../../vendor/autoload.php';

call_user_func(function () {
    $reader = new CsvReader(__DIR__.'/../../data/geojson/zips.csv');

    /** @var array<int, string> $zipsToState */
    $zipsToState = [];

    foreach ($reader as $row) {
        [$zip, $state] = $row;

        if (is_string($zip)) {
            $zip = preg_replace('/^\x{FEFF}/u', '', $zip);
        }

        Assert::numeric($zip);
        Assert::scalar($state);

        $zipsToState[(int) $zip] = State::from((string) $state)->value;
    }

    Assert::notEmpty($zipsToState);
    $zips = array_keys($zipsToState);
    $minZip = min($zips);
    $maxZip = max($zips);

    $geoJsonPtr = fopen(__DIR__.'/../../data/geojson/zip-code-tabulation-area.json', 'r');
    Assert::resource($geoJsonPtr);

    /** @var array<string, resource> $writersByState */
    $writersByState = [];

    while (false !== ($line = fgets($geoJsonPtr))) {
        if (!preg_match('/"ZCTA5CE20"\s*:\s*"(\d+)"/', $line, $matches)) {
            continue;
        }

        $zcta = $matches[1];
        Assert::numeric($zcta);
        $zip = (int) $zcta;

        $state = $zipsToState[$zip] ?? null;

        if (null === $state) {
            // no exact match. Try to find a neighboring zip code
            $j = $zip;
            $k = $zip;

            do {
                $state = $zipsToState[--$j] ?? ($zipsToState[++$k] ?? null);
            } while (null === $state && ($j >= $minZip || $k <= $maxZip));

            Assert::notNull($state, sprintf('Could not match ZCTA "%s" to a state', $zcta));
        }

        $outLine = rtrim(trim($line), ',');

        if (isset($writersByState[$state])) {
            $outLine = ",\n$outLine";
        } else {
            $outLine = "\n$outLine";
        }

        $writersByState[$state] ??= openGeoJsonWriter($state);
        fwrite($writersByState[$state], $outLine);
    }

    Assert::true(feof($geoJsonPtr));
    fclose($geoJsonPtr);
    array_walk($writersByState, closeGeoJsonWriter(...));
});

/**
 * @param resource $ptr
 */
function closeGeoJsonWriter($ptr): void
{
    fwrite($ptr, "\n]}");
    fclose($ptr);
}

/**
 * @return resource
 */
function openGeoJsonWriter(string $state)
{
    $filename = __DIR__.'/../../web-data/geojson/'.strtolower($state).'.json';
    FileUtilities::ensureFileDirectory($filename);
    $ptr = fopen($filename, 'w');
    Assert::resource($ptr);
    fwrite($ptr, '{"type":"FeatureCollection", "features": [');

    return $ptr;
}
