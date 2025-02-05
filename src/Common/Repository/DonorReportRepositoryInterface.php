<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Repository;

use CliffordVickrey\Book2024\Common\Entity\Report\DonorReport;
use CliffordVickrey\Book2024\Common\Enum\CampaignType;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;
use CliffordVickrey\Book2024\Common\Enum\State;

interface DonorReportRepositoryInterface
{
    public function get(
        CampaignType $campaignType = CampaignType::donald_trump,
        State $state = State::USA,
        ?DonorCharacteristic $characteristicA = null,
        ?DonorCharacteristic $characteristicB = null,
    ): DonorReport;

    public function save(DonorReport $report): void;

    public function deleteAll(): void;
}
