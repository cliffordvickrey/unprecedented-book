#!/usr/bin/php
<?php

use CliffordVickrey\Book2024\Common\Csv\CsvWriter;
use CliffordVickrey\Book2024\Common\Entity\Combined\DonorPanel;
use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\Report\CountyReportRow;
use CliffordVickrey\Book2024\Common\Entity\Report\ZipReportRow;
use CliffordVickrey\Book2024\Common\Repository\CountyRepository;
use CliffordVickrey\Book2024\Common\Repository\DonorPanelRepository;
use CliffordVickrey\Book2024\Common\Repository\ZipCodeRepository;
use CliffordVickrey\Book2024\Common\Service\DonorProfileService;

ini_set('memory_limit', '-1');

chdir(__DIR__);
require_once __DIR__.'/../../vendor/autoload.php';

call_user_func(function () {
    $countyRepository = new CountyRepository();
    $donorPanelRepository = new DonorPanelRepository();
    $profiler = new DonorProfileService();
    $zipCodeRepository = new ZipCodeRepository();

    $panels = $donorPanelRepository->get();

    /** @var array<string, CountyReportRow> $countyReportRows */
    $countyReportRows = [];
    /** @var array<string, ZipReportRow> $zipReportRows */
    $zipReportRows = [];

    // build the reports...
    foreach ($panels as $panel) {
        /** @var DonorPanel $panel */
        $zip = $panel->donor->zip;

        if (!$zipCodeRepository->hasZip($zip)) {
            continue;
        }

        $zipObj = $zipCodeRepository->getByZip($zip);

        if ($zipObj->state !== $panel->donor->state) {
            continue;
        }

        $countyObj = $zipObj->county ? $countyRepository->getAggregate($zipObj->county) : null;
        $countyReportRow = null;

        if ($countyObj && !isset($countyReportRows[$countyObj->slug])) {
            printf('[county] Adding %s (%s)%s', $countyObj->name, $countyObj->state, \PHP_EOL);
            $countyReportRows[$countyObj->slug] = CountyReportRow::fromCounty($countyObj);
        }

        if ($countyObj) {
            $countyReportRow = $countyReportRows[$countyObj->slug];
        }

        if (!isset($zipReportRows[$zipObj->slug])) {
            printf('[zip] Adding %s (%s)%s', $zipObj->zipcode, $zipObj->state, \PHP_EOL);
            $zipReportRows[$zipObj->slug] = ZipReportRow::fromZipCodeAndCounty($zipObj, $countyObj);
        }

        $zipReportRow = $zipReportRows[$zipObj->slug];

        $profile = $profiler->buildDonorProfile($panel);
        $countyReportRow?->setProfile($profile);
        $zipReportRow->setProfile($profile);
    }

    ksort($countyReportRows);
    ksort($zipReportRows);

    // and then write 'em
    writeReport($countyReportRows, CountyReportRow::class);
    writeReport($zipReportRows, ZipReportRow::class);
});

/**
 * @param array<array-key, TReport> $reportRows
 * @param class-string<TReport>     $prototype
 *
 * @template TReport of CountyReportRow|ZipReportRow
 */
function writeReport(array $reportRows, string $prototype): void
{
    $slug = CountyReportRow::class === $prototype ? 'county' : 'zip';

    $filename = __DIR__.sprintf('/../../data/report/%s-report.csv', $slug);

    $writer = new CsvWriter($filename);

    $headers = call_user_func([$prototype, 'headers']);
    $writer->write($headers);

    array_walk($reportRows, fn (Entity $row) => $writer->write($row->toArray(true)));

    $writer->close();
}
