#!/usr/bin/php
<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\Common\Entity\Combined\DonorPanel;
use CliffordVickrey\Book2024\Common\Entity\Report\DonorReport;
use CliffordVickrey\Book2024\Common\Entity\Report\DonorReportValue;
use CliffordVickrey\Book2024\Common\Enum\CampaignType;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;
use CliffordVickrey\Book2024\Common\Enum\State;
use CliffordVickrey\Book2024\Common\Repository\DonorPanelRepository;
use CliffordVickrey\Book2024\Common\Service\DonorProfileService;

ini_set('memory_limit', '-1');

chdir(__DIR__);
require_once __DIR__.'/../../vendor/autoload.php';

call_user_func(function () {
    $donorPanelRepository = new DonorPanelRepository();
    $profiler = new DonorProfileService();

    $reports = DonorReport::collectNew();

    $panels = $donorPanelRepository->get();

    foreach ($panels as $panel) {
        /** @var DonorPanel $panel */
        $profile = $profiler->buildDonorProfile($panel);

        $characteristicsByCampaign = $profiler->collectDonorCharacteristics($profile);

        $states = [$profile->state];

        if (State::USA !== $profile->state) {
            $states[] = State::USA;
        }

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
});
