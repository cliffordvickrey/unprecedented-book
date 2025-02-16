#!/usr/bin/php
<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\Common\Entity\Combined\DonorPanel;
use CliffordVickrey\Book2024\Common\Entity\Report\AbstractReport;
use CliffordVickrey\Book2024\Common\Entity\Report\AbstractReportCollection;
use CliffordVickrey\Book2024\Common\Entity\Report\CampaignReport;
use CliffordVickrey\Book2024\Common\Entity\Report\CampaignReportCollection;
use CliffordVickrey\Book2024\Common\Entity\Report\DonorReport;
use CliffordVickrey\Book2024\Common\Entity\Report\DonorReportCollection;
use CliffordVickrey\Book2024\Common\Entity\Report\DonorReportValue;
use CliffordVickrey\Book2024\Common\Entity\Report\MapReport;
use CliffordVickrey\Book2024\Common\Entity\Report\MapReportCollection;
use CliffordVickrey\Book2024\Common\Entity\Report\ReportValue;
use CliffordVickrey\Book2024\Common\Enum\CampaignType;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;
use CliffordVickrey\Book2024\Common\Enum\State;
use CliffordVickrey\Book2024\Common\Exception\BookUnexpectedValueException;
use CliffordVickrey\Book2024\Common\Repository\CampaignReportRepository;
use CliffordVickrey\Book2024\Common\Repository\DonorPanelRepository;
use CliffordVickrey\Book2024\Common\Repository\DonorReportRepository;
use CliffordVickrey\Book2024\Common\Repository\MapReportRepository;
use CliffordVickrey\Book2024\Common\Service\DonorProfileService;

ini_set('memory_limit', '-1');

chdir(__DIR__);
require_once __DIR__.'/../../vendor/autoload.php';

call_user_func(function () {
    $donorPanelRepository = new DonorPanelRepository();
    $profiler = new DonorProfileService();

    $campaignReports = CampaignReport::collectNew();
    $donorReports = DonorReport::collectNew();
    $mapReports = MapReport::collectNew();

    $panels = $donorPanelRepository->get();

    $oldState = null;

    // build the reports...
    foreach ($panels as $panel) {
        /** @var DonorPanel $panel */
        $profile = $profiler->buildDonorProfile($panel);

        $zip = sprintf('%05d', substr($panel->donor->zip, 0, 5));

        if ('00000' === $zip) {
            $zip = null;
        }

        $characteristicsByCampaign = $profiler->collectDonorCharacteristics($profile);

        $states = [$profile->state];

        if (State::USA !== $profile->state) {
            $states[] = State::USA;
        }

        if ($oldState && $oldState !== $profile->state) {
            printf('Profiling donors in %s...%s', $profile->state->getDescription(), \PHP_EOL);

            if (State::USA !== $oldState) {
                flushReports($campaignReports, $donorReports, $mapReports, $oldState);
            }
        }

        $oldState = $profile->state;

        foreach ($characteristicsByCampaign as $campaignStr => $characteristics) {
            $campaignValuesToAdd = array_map(
                ReportValue::fromDonorProfileAmount(...),
                $profile->campaigns[$campaignStr]->donationsByDate
            );

            $donorValueToAdd = DonorReportValue::fromDonorProfileAmount($profile->campaigns[$campaignStr]->total);
            $mapValueToAdd = ReportValue::fromDonorProfileAmount($profile->campaigns[$campaignStr]->total);

            $campaign = CampaignType::from($campaignStr);

            $characteristicsA = $characteristics;
            $characteristicsB = $characteristics;

            foreach ($states as $state) {
                // all donors
                $key = AbstractReport::inflectForKey($campaign, $state);
                $campaignReports[$key]->addMultiple($campaignValuesToAdd);
                $donorReports[$key]->add($donorValueToAdd, $characteristics);

                if (State::USA !== $state) {
                    $keySansState = AbstractReport::inflectForKey($campaign);

                    // donors by state
                    $mapReports[$keySansState]->add($mapValueToAdd, $state->value);

                    if (null !== $zip) {
                        // donors by zip
                        $mapReports[$key]->add($mapValueToAdd, $zip);
                    }
                }

                foreach ($characteristicsA as $characteristicA) {
                    /** @var DonorCharacteristic $characteristicA */
                    if ($characteristicA->isMutuallyExclusiveOrTautologicalWith($campaign)) {
                        continue;
                    }

                    // donors with one characteristic
                    $key = AbstractReport::inflectForKey($campaign, $state, $characteristicA);
                    $campaignReports[$key]->addMultiple($campaignValuesToAdd);
                    $donorReports[$key]->add($donorValueToAdd, $characteristics);

                    if (State::USA !== $state) {
                        $keySansState = AbstractReport::inflectForKey($campaign, characteristicA: $characteristicA);

                        // donors by state
                        $mapReports[$keySansState]->add($mapValueToAdd, $state->value);

                        if (null !== $zip) {
                            // donors by zip
                            $mapReports[$key]->add($mapValueToAdd, $zip);
                        }
                    }

                    foreach ($characteristicsB as $characteristicB) {
                        // donors with two characteristics
                        /** @var DonorCharacteristic $characteristicB */
                        if ($characteristicB->isMutuallyExclusiveOrTautologicalWith($characteristicA, $campaign)) {
                            continue;
                        }

                        $key = AbstractReport::inflectForKey($campaign, $state, $characteristicA, $characteristicB);
                        $campaignReports[$key]->addMultiple($campaignValuesToAdd);
                        $donorReports[$key]->add($donorValueToAdd, $characteristics);

                        if (State::USA !== $state) {
                            $keySansState = AbstractReport::inflectForKey(
                                $campaign,
                                characteristicA: $characteristicA,
                                characteristicB: $characteristicB
                            );

                            // donors by state
                            $mapReports[$keySansState]->add($mapValueToAdd, $state->value);

                            if (null !== $zip) {
                                // donors by zip
                                $mapReports[$key]->add($mapValueToAdd, $zip);
                            }
                        }
                    }
                }
            }
        }
    }

    flushReports($campaignReports, $donorReports, $mapReports);
});

/**
 * To save memory, write one state's reports at a time. When we're finished, flush the entire buffer of reports.
 *
 * @param array<string, CampaignReport> $campaignReports
 * @param array<string, DonorReport>    $donorReports
 * @param array<string, MapReport>      $mapReports
 */
function flushReports(
    array &$campaignReports,
    array &$donorReports,
    array &$mapReports,
    ?State $state = null,
): void {
    $keys = array_keys($campaignReports);

    if (null !== $state) {
        $keys = array_filter($keys, static function (string $key) use ($state): bool {
            $parts = explode('-', $key);

            $statePart = $parts[1] ?? '';

            return $state->value === strtoupper($statePart);
        });
    }

    $campaignReportsToFlush = [];
    $donorReportsToFlush = [];
    $mapReportsToFlush = [];

    foreach ($keys as $key) {
        $campaignReportsToFlush[$key] = $campaignReports[$key];
        unset($campaignReports[$key]);
        $donorReportsToFlush[$key] = $donorReports[$key];
        unset($donorReports[$key]);
        $mapReportsToFlush[$key] = $mapReports[$key];
        unset($mapReports[$key]);
    }

    // ...reduce them to collections (stored in each file)
    printf('Collecting...%s', \PHP_EOL);
    $collections = [
        ...collectReports($campaignReportsToFlush, CampaignReport::class),
        ...collectReports($donorReportsToFlush, DonorReport::class),
        ...collectReports($mapReportsToFlush, MapReport::class),
    ];

    // and save 'em
    array_walk($collections, saveCollection(...));
}

function saveCollection(CampaignReportCollection|DonorReportCollection|MapReportCollection $collection): void
{
    /** @phpstan-var CampaignReportRepository $campaignRepositoryCompressed */
    static $campaignRepositoryCompressed = new CampaignReportRepository();
    /** @phpstan-var CampaignReportRepository $campaignRepositoryUnCompressed */
    static $campaignRepositoryUnCompressed = new CampaignReportRepository(compressionLevel: null);
    /** @phpstan-var DonorReportRepository $donorRepositoryCompressed */
    static $donorRepositoryCompressed = new DonorReportRepository();
    /** @phpstan-var DonorReportRepository $donorRepositoryUnCompressed */
    static $donorRepositoryUnCompressed = new DonorReportRepository(compressionLevel: null);
    /** @phpstan-var MapReportRepository $mapRepositoryCompressed */
    static $mapRepositoryCompressed = new MapReportRepository();
    /** @phpstan-var MapReportRepository $mapRepositoryUnCompressed */
    static $mapRepositoryUnCompressed = new MapReportRepository(compressionLevel: null);

    /** @phpstan-var bool $hasDeleted */
    static $hasDeleted = false;

    if (!$hasDeleted) {
        printf('Deleting old files...%s', \PHP_EOL);
        $campaignRepositoryCompressed->deleteAll();
        $campaignRepositoryUnCompressed->deleteAll();
        $donorRepositoryCompressed->deleteAll();
        $donorRepositoryUnCompressed->deleteAll();
        $mapRepositoryCompressed->deleteAll();
        $mapRepositoryUnCompressed->deleteAll();
        $hasDeleted = true;
        printf('Saving new files...%s', \PHP_EOL);
    }

    if ($collection instanceof CampaignReportCollection) {
        $campaignRepositoryCompressed->saveCollection($collection);
        $campaignRepositoryUnCompressed->saveCollection($collection);

        return;
    } elseif ($collection instanceof MapReportCollection) {
        $mapRepositoryCompressed->saveCollection($collection);
        $mapRepositoryUnCompressed->saveCollection($collection);

        return;
    }

    $donorRepositoryCompressed->saveCollection($collection);
    $donorRepositoryUnCompressed->saveCollection($collection);
}

/**
 * @param array<string, TReport> $reports
 * @param class-string<TReport>  $classStr
 *
 * @return list<CampaignReportCollection|DonorReportCollection|MapReportCollection>
 *
 * @template TReport of CampaignReport|DonorReport|MapReport
 */
function collectReports(array $reports, string $classStr): array
{
    /** @phpstan-var AbstractReportCollection<TReport> $collectionClassStr */
    $collectionClassStr = match ($classStr) { // @phpstan-ignore-line
        CampaignReport::class => CampaignReportCollection::class,
        DonorReport::class => DonorReportCollection::class,
        MapReport::class => MapReportCollection::class,
        default => throw new BookUnexpectedValueException(),
    };

    $arr = array_reduce($reports, function (array $carry, AbstractReport $report) use ($collectionClassStr): array {
        if ($report instanceof DonorReport) {
            $report->setPercentages();
        } elseif ($report instanceof CampaignReport) {
            $report->sortByIndex();
        }

        $key = $report->getKey();

        $parts = explode('-', $key);
        array_pop($parts);
        $key = implode('-', $parts);

        /** @var array<string, AbstractReportCollection<TReport>> $carry */
        $reports = $carry[$key] ?? new $collectionClassStr();

        $reports->reports[$report->characteristicB->value ?? AbstractReport::ALL] = $report;
        $carry[$key] = $reports;

        return $carry;
    }, []);

    /** @var array<string, CampaignReportCollection|DonorReportCollection> $arr */
    return array_values($arr);
}
