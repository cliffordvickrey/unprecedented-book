<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Repository;

use CliffordVickrey\Book2024\Common\Entity\Report\CampaignReport;

/**
 * @extends AbstractReportRepository<CampaignReport>
 */
final readonly class CampaignReportRepository extends AbstractReportRepository
{
    protected function getClassStr(): string
    {
        return CampaignReport::class;
    }
}
