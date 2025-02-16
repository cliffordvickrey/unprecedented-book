<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Repository;

use CliffordVickrey\Book2024\Common\Entity\Report\MapReport;

/**
 * @extends AbstractReportRepository<MapReport>
 */
final readonly class MapReportRepository extends AbstractReportRepository
{
    protected function getClassStr(): string
    {
        return MapReport::class;
    }
}
