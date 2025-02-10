<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Repository;

use CliffordVickrey\Book2024\Common\Entity\Report\DonorReport;

/**
 * @extends AbstractReportRepository<DonorReport>
 */
final readonly class DonorReportRepository extends AbstractReportRepository
{
    protected function getClassStr(): string
    {
        return DonorReport::class;
    }
}
