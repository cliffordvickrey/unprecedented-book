#!/usr/bin/php
<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\Common\Entity\Combined\DonorPanel;
use CliffordVickrey\Book2024\Common\Entity\Report\DonorReport;
use CliffordVickrey\Book2024\Common\Entity\Report\DonorReportCollection;
use CliffordVickrey\Book2024\Common\Entity\Report\DonorReportValue;
use CliffordVickrey\Book2024\Common\Enum\CampaignType;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;
use CliffordVickrey\Book2024\Common\Enum\State;
use CliffordVickrey\Book2024\Common\Repository\DonorPanelRepository;
use CliffordVickrey\Book2024\Common\Repository\DonorReportRepository;
use CliffordVickrey\Book2024\Common\Service\DonorProfileService;

ini_set('memory_limit', '-1');

chdir(__DIR__);
require_once __DIR__.'/../../vendor/autoload.php';

call_user_func(function () {
    $donorPanelRepository = new DonorPanelRepository();
    $profiler = new DonorProfileService();

    $reports = DonorReport::collectNew();

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
            $valueToAdd = DonorReportValue::fromDonorProfileAmount($profile->campaigns[$campaignStr]->total);

            $campaign = CampaignType::from($campaignStr);

            $characteristicsA = $characteristics;
            $characteristicsB = $characteristics;

            foreach ($states as $state) {
                // all donors
                $key = DonorReport::inflectForKey($campaign, $state);
                $reports[$key]->add($valueToAdd, $characteristics);

                foreach ($characteristicsA as $characteristicA) {
                    // donors with one characteristic
                    $key = DonorReport::inflectForKey($campaign, $state, $characteristicA);
                    $reports[$key]->add($valueToAdd, $characteristics);

                    foreach ($characteristicsB as $characteristicB) {
                        // donors with two characteristics
                        /** @var DonorCharacteristic $characteristicB */
                        if ($characteristicB->isMutuallyExclusive($characteristicA)) {
                            continue;
                        }

                        $key = DonorReport::inflectForKey($campaign, $state, $characteristicA, $characteristicB);
                        $reports[$key]->add($valueToAdd, $characteristics);
                    }
                }
            }
        }
    }

    // ...reduce them to collections (stored in each file)
    printf('Saving...%s', \PHP_EOL);

    $collections = array_reduce($reports, function (array $carry, DonorReport $report): array {
        $report->setPercentages();

        $key = $report->getKey();

        $parts = explode('-', $key);
        array_pop($parts);
        $key = implode('-', $parts);

        /** @var array<string, DonorReportCollection> $carry */
        $reports = $carry[$key] ?? new DonorReportCollection();

        $reports->donorReports[$report->characteristicB->value ?? DonorReport::ALL] = $report;
        $carry[$key] = $reports;

        return $carry;
    }, []);

    // ...and save 'em
    $donorReportRepository = new DonorReportRepository();
    $donorReportRepository->deleteAll();
    array_walk(
        $collections,
        static fn (DonorReportCollection $reports) => $donorReportRepository->saveCollection($reports)
    );
});
