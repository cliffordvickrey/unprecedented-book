<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Report;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;
use Webmozart\Assert\Assert;

class DonorReportCollection extends Entity
{
    /** @var array<string, DonorReport> */
    public array $donorReports = [];

    public function hasByCharacteristic(?DonorCharacteristic $characteristic): bool
    {
        return isset($this->donorReports[$characteristic->value ?? DonorReport::ALL]);
    }

    public function getByCharacteristic(?DonorCharacteristic $characteristic): DonorReport
    {
        $report = $this->donorReports[$characteristic->value ?? DonorReport::ALL] ?? null;
        Assert::notNull($report);

        return $report;
    }
}
