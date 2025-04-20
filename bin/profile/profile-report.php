#!/usr/bin/php
<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\Common\Csv\CsvWriter;
use CliffordVickrey\Book2024\Common\Entity\Combined\DonorPanel;
use CliffordVickrey\Book2024\Common\Entity\Combined\ReceiptInPanel;
use CliffordVickrey\Book2024\Common\Enum\CampaignType;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;
use CliffordVickrey\Book2024\Common\Repository\DonorPanelRepository;
use CliffordVickrey\Book2024\Common\Service\DonorProfileService;

ini_set('memory_limit', '-1');

chdir(__DIR__);
require_once __DIR__.'/../../vendor/autoload.php';

call_user_func(function () {
    $donorPanelRepository = new DonorPanelRepository();
    $profiler = new DonorProfileService();

    $panels = $donorPanelRepository->get();

    $headers = [
        'id',
        'donor_id',
        'donor_state',
        'donor_zip',
        'recipient_id',
        'receipt_amt',
        'receipt_date',
        ...array_map(static fn (DonorCharacteristic $char) => $char->value, DonorCharacteristic::cases()),
    ];

    $protoType = array_combine($headers, array_fill(0, count($headers), 0));

    $counter = 0;

    $file = __DIR__.'/../../data/report/profile_report.csv';
    $writer = new CsvWriter($file);
    $writer->write($headers);

    $trumpSlugs = $profiler->getCampaignCommitteeSlugs(CampaignType::donald_trump);
    $bidenSlugs = $profiler->getCampaignCommitteeSlugs(CampaignType::joe_biden);
    $harrisSlugs = $profiler->getCampaignCommitteeSlugs(CampaignType::kamala_harris);

    $map = [
        1 => array_combine($trumpSlugs, $trumpSlugs),
        2 => array_combine($bidenSlugs, $bidenSlugs),
        3 => array_combine($harrisSlugs, $harrisSlugs),
    ];

    // build the reports...
    foreach ($panels as $panel) {
        /** @var DonorPanel $panel */
        $profile = $profiler->buildDonorProfile($panel);

        $characteristicsByCampaign = $profiler->collectDonorCharacteristics($profile);

        foreach ($characteristicsByCampaign as $campaignTypeStr => $characteristics) {
            $campaignType = CampaignType::from($campaignTypeStr);

            $recipientId = match ($campaignType) {
                CampaignType::donald_trump => 1,
                CampaignType::joe_biden => 2,
                CampaignType::kamala_harris => 3,
            };

            $rowPrototype = $protoType;

            $rowPrototype['donor_id'] = $panel->donor->id;
            $rowPrototype['donor_state'] = $panel->donor->state;
            $rowPrototype['donor_zip'] = $panel->donor->zip;
            $rowPrototype['recipient_id'] = $recipientId;

            array_walk(
                $characteristics,
                static function (DonorCharacteristic $characteristic) use (&$rowPrototype): void {
                    $rowPrototype[$characteristic->value] = 1;
                }
            );

            $receipts = array_filter(
                $panel->receipts,
                static fn (ReceiptInPanel $receipt) => isset($map[$recipientId][$receipt->recipientSlug]),
            );

            array_walk($receipts, function (ReceiptInPanel $receiptInPanel) use (&$counter, $rowPrototype, $writer) {
                $row = $rowPrototype;
                $row['id'] = ++$counter;
                $row['receipt_amt'] = $receiptInPanel->amount;
                $row['receipt_date'] = $receiptInPanel->date->format('Y-m-d');
                $writer->write(array_values($row));
            });
        }
    }
});
