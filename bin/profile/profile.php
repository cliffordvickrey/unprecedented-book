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
use CliffordVickrey\Book2024\Common\Entity\Report\ReportValue;
use CliffordVickrey\Book2024\Common\Enum\CampaignType;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;
use CliffordVickrey\Book2024\Common\Enum\State;
use CliffordVickrey\Book2024\Common\Exception\BookUnexpectedValueException;
use CliffordVickrey\Book2024\Common\Repository\CampaignReportRepository;
use CliffordVickrey\Book2024\Common\Repository\DonorPanelRepository;
use CliffordVickrey\Book2024\Common\Repository\DonorReportRepository;
use CliffordVickrey\Book2024\Common\Service\DonorProfileService;

ini_set('memory_limit', '-1');

chdir(__DIR__);
require_once __DIR__.'/../../vendor/autoload.php';

call_user_func(function () {
    $donorPanelRepository = new DonorPanelRepository();
    $profiler = new DonorProfileService();

    $campaignReports = CampaignReport::collectNew();
    $donorReports = DonorReport::collectNew();

    $panels = $donorPanelRepository->get();

    $oldState = null;

    // build the reports...
    foreach ($panels as $panel) {
        /** @var DonorPanel $panel */
        $profile = $profiler->buildDonorProfile($panel);

        $characteristicsByCampaign = $profiler->collectDonorCharacteristics($profile);

        $states = [$profile->state];

        if (State::USA !== $profile->state) {
            $states[] = State::USA;
        }

        if ($oldState !== $profile->state) {
            printf('Profiling donors in %s...%s', $profile->state->getDescription(), \PHP_EOL);
        }

        $oldState = $profile->state;

        foreach ($characteristicsByCampaign as $campaignStr => $characteristics) {
            $campaignValuesToAdd = array_map(
                ReportValue::fromDonorProfileAmount(...),
                $profile->campaigns[$campaignStr]->donationsByDate
            );

            $donorValueToAdd = DonorReportValue::fromDonorProfileAmount($profile->campaigns[$campaignStr]->total);

            $campaign = CampaignType::from($campaignStr);

            $characteristicsA = $characteristics;
            $characteristicsB = $characteristics;

            foreach ($states as $state) {
                // all donors
                $key = AbstractReport::inflectForKey($campaign, $state);
                $campaignReports[$key]->addMultiple($campaignValuesToAdd);
                $donorReports[$key]->add($donorValueToAdd, $characteristics);

                foreach ($characteristicsA as $characteristicA) {
                    /** @var DonorCharacteristic $characteristicA */
                    if ($characteristicA->isMutuallyExclusiveOrTautologicalWith($campaign)) {
                        continue;
                    }

                    // donors with one characteristic
                    $key = AbstractReport::inflectForKey($campaign, $state, $characteristicA);
                    $campaignReports[$key]->addMultiple($campaignValuesToAdd);
                    $donorReports[$key]->add($donorValueToAdd, $characteristics);

                    foreach ($characteristicsB as $characteristicB) {
                        // donors with two characteristics
                        /** @var DonorCharacteristic $characteristicB */
                        if ($characteristicB->isMutuallyExclusiveOrTautologicalWith($characteristicA, $campaign)) {
                            continue;
                        }

                        $key = AbstractReport::inflectForKey($campaign, $state, $characteristicA, $characteristicB);
                        $campaignReports[$key]->addMultiple($campaignValuesToAdd);
                        $donorReports[$key]->add($donorValueToAdd, $characteristics);
                    }
                }
            }
        }
    }

    // ...reduce them to collections (stored in each file)
    printf('Collecting...%s', \PHP_EOL);
    $collections = [
        ...collectReports($campaignReports, CampaignReport::class),
        ...collectReports($donorReports, DonorReport::class),
    ];

    // and save 'em
    array_walk($collections, saveCollection(...));
});

function saveCollection(CampaignReportCollection|DonorReportCollection $collection): void
{
    /** @phpstan-var CampaignReportRepository $campaignRepositoryCompressed */
    static $campaignRepositoryCompressed = new CampaignReportRepository();
    /** @phpstan-var CampaignReportRepository $campaignRepositoryUnCompressed */
    static $campaignRepositoryUnCompressed = new CampaignReportRepository(compressionLevel: null);
    /** @phpstan-var DonorReportRepository $donorRepositoryCompressed */
    static $donorRepositoryCompressed = new DonorReportRepository();
    /** @phpstan-var DonorReportRepository $donorRepositoryUnCompressed */
    static $donorRepositoryUnCompressed = new DonorReportRepository(compressionLevel: null);
    /** @phpstan-var bool $hasDeleted */
    static $hasDeleted = false;

    if (!$hasDeleted) {
        printf('Deleting old files...%s', \PHP_EOL);
        $campaignRepositoryCompressed->deleteAll();
        $campaignRepositoryUnCompressed->deleteAll();
        $donorRepositoryCompressed->deleteAll();
        $donorRepositoryUnCompressed->deleteAll();
        $hasDeleted = true;
        printf('Saving new files...%s', \PHP_EOL);
    }

    if ($collection instanceof CampaignReportCollection) {
        $campaignRepositoryCompressed->saveCollection($collection);
        $campaignRepositoryUnCompressed->saveCollection($collection);

        return;
    }

    $donorRepositoryCompressed->saveCollection($collection);
    $donorRepositoryUnCompressed->saveCollection($collection);
}

/**
 * @param array<string, TReport> $reports
 * @param class-string<TReport>  $classStr
 *
 * @return list<CampaignReportCollection|DonorReportCollection>
 *
 * @template TReport of CampaignReport|DonorReport
 */
function collectReports(array $reports, string $classStr): array
{
    /** @phpstan-var AbstractReportCollection<TReport> $collectionClassStr */
    $collectionClassStr = match ($classStr) { // @phpstan-ignore-line
        CampaignReport::class => CampaignReportCollection::class,
        DonorReport::class => DonorReportCollection::class,
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
