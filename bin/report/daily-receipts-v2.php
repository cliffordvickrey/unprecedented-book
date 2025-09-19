<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\Common\Csv\CsvWriter;
use CliffordVickrey\Book2024\Common\Entity\Combined\DonorPanel;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle2024;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\RecipientAttribute;
use CliffordVickrey\Book2024\Common\Enum\CampaignType;
use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeType;
use CliffordVickrey\Book2024\Common\Enum\PacType;
use CliffordVickrey\Book2024\Common\Repository\CommitteeAggregateRepository;
use CliffordVickrey\Book2024\Common\Repository\DonorPanelRepository;
use CliffordVickrey\Book2024\Common\Service\DonorProfileService;
use CliffordVickrey\Book2024\Common\Utilities\DateUtilities;
use Webmozart\Assert\Assert;

ini_set('memory_limit', '-1');

require_once __DIR__ . '/../../vendor/autoload.php';
chdir(__DIR__);

call_user_func(function () {
    $reflectionClass = new ReflectionClass(DonorProfileCycle2024::class);

    $reflectionProp = $reflectionClass->getProperty('presJoeBiden');

    $attr = ($reflectionProp->getAttributes(RecipientAttribute::class)[0] ?? null)?->newInstance();

    Assert::isInstanceOf($attr, RecipientAttribute::class);

    /** @var RecipientAttribute $attr */
    $bidenCommitteeIds = $attr->committeeIds;
    $bidenPacs = [];
    $bidenSuperPacs = [];

    $repo = new CommitteeAggregateRepository();

    $committees = array_map(
        static fn(string $committeeId) => $repo->getByCommitteeId($committeeId),
        $bidenCommitteeIds
    );

    foreach ($committees as $committee) {
        $pacType = PacType::fromCommitteeType($committee->getLastCommittee()->CMTE_TP ?? CommitteeType::P);

        $isSuperPac = PacType::superPac === $pacType;

        if ($isSuperPac) {
            $bidenSuperPacs[$committee->slug] = true;
        } else {
            $bidenPacs[$committee->slug] = true;
        }
    }

    $profiler = new DonorProfileService();

    $donorPanelRepository = new DonorPanelRepository();
    $panels = $donorPanelRepository->get();

    $headers = [
        'date',
        'under_200_pac_amt',
        'under_200_pac_donors',
        'under_200_pac_receipts',
        '200_to_999_pac_amt',
        '200_to_999_pac_donors',
        '200_to_999_pac_receipts',
        '1000_or_more_pac_amt',
        '1000_or_more_pac_donors',
        '1000_or_more_pac_receipts',
        'under_200_super_pac_amt',
        'under_200_super_pac_donors',
        'under_200_super_pac_receipts',
        '200_to_999_super_pac_amt',
        '200_to_999_super_pac_donors',
        '200_to_999_super_pac_receipts',
        '1000_or_more_super_pac_amt',
        '1000_or_more_super_pac_donors',
        '1000_or_more_super_pac_receipts'
    ];

    $startDate = $attr->startDate;
    Assert::notNull($startDate);
    $endDate = $attr->endDate;
    Assert::notNull($endDate);

    $dates = DateUtilities::getDateRanges($startDate, $endDate);

    $reportData = array_reduce($dates, function (array $carry, DateTimeImmutable $dt) use ($headers) {
        $now = $dt->format('Y-m-d');

        $data = array_combine($headers, array_fill(0, count($headers), 0));

        $data['date'] = $now;

        /** @var array<string, array<string, mixed>> $carry */
        $carry[$now] = $data;

        return $carry;
    }, []);

    // build the reports...
    foreach ($panels as $panel) {
        /** @var DonorPanel $panel */
        $profile = $profiler->buildDonorProfile($panel);

        $bidenCampaignProfile = $profile->campaigns[CampaignType::joe_biden->value] ?? null;

        if (null === $bidenCampaignProfile) {
            continue;
        }

        $isUnder200 = $bidenCampaignProfile->total->amount < 200.0;
        $isBetween200And999 = !$isUnder200 && $bidenCampaignProfile->total->amount < 1000.0;

        $leading = match (true) {
            $isUnder200 => 'under_200',
            $isBetween200And999 => '200_to_999',
            default => '1000_or_more',
        };

        $dtMemo = [];

        foreach ($panel->receipts as $receipt) {
            $isBidenPac = $bidenPacs[$receipt->recipientSlug] ?? false;
            $isBidenSuperPac = $bidenSuperPacs[$receipt->recipientSlug] ?? false;

            if (!($isBidenPac || $isBidenSuperPac)) {
                continue;
            }

            $trailing = $isBidenPac ? 'pac' : 'super_pac';

            $key = "{$leading}_$trailing";

            $dt = $receipt->date->format('Y-m-d');

            if (!isset($reportData[$dt])) {
                continue;
            }

            /** @var array<string, int|float> $totals */
            $totals = $reportData[$dt];

            $totals[$key . '_amt'] += $receipt->amount;
            $totals[$key . '_donors'] += (isset($dtMemo[$dt]) ? 0 : 1);
            ++$totals[$key . '_receipts'];

            $reportData[$dt] = $totals;

            $dtMemo[$dt] = true;
        }
    }

    $outputFile = __DIR__ . '/../../data/report/biden-daily-receipts.csv';

    $writer = new CsvWriter($outputFile);

    $writer->write($headers);

    array_walk($reportData, static fn(array $row) => $writer->write(array_values($row)));

    $writer->close();
});
