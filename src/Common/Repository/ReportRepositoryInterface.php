<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Repository;

use CliffordVickrey\Book2024\Common\Entity\Report\AbstractReport;
use CliffordVickrey\Book2024\Common\Entity\Report\AbstractReportCollection;
use CliffordVickrey\Book2024\Common\Enum\CampaignType;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;
use CliffordVickrey\Book2024\Common\Enum\State;

/**
 * @template TReport of AbstractReport
 */
interface ReportRepositoryInterface
{
    /**
     * @phpstan-return TReport
     */
    public function get(
        CampaignType $campaignType = CampaignType::donald_trump,
        State $state = State::USA,
        ?DonorCharacteristic $characteristicA = null,
        ?DonorCharacteristic $characteristicB = null,
    ): AbstractReport;

    /**
     * @param AbstractReportCollection<TReport> $reports
     */
    public function saveCollection(AbstractReportCollection $reports): void;

    /**
     * @phpstan-param TReport $report
     */
    public function save(AbstractReport $report): void;

    public function deleteAll(): void;
}
