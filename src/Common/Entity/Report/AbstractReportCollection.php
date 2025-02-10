<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Report;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;
use Webmozart\Assert\Assert;

/**
 * @template TReport of AbstractReport
 */
abstract class AbstractReportCollection extends Entity
{
    /** @var array<string, TReport> */
    public array $reports = [];

    public function hasByCharacteristic(?DonorCharacteristic $characteristic): bool
    {
        return isset($this->reports[$characteristic->value ?? AbstractReport::ALL]);
    }

    /**
     * @phpstan-return TReport
     */
    public function getByCharacteristic(?DonorCharacteristic $characteristic): AbstractReport
    {
        $report = $this->reports[$characteristic->value ?? AbstractReport::ALL] ?? null;
        Assert::notNull($report);

        return $report;
    }

    public function getKey(): string
    {
        Assert::notEmpty($this->reports);

        $key = $this->reports[array_key_first($this->reports)]->getKey();

        $parts = explode('-', $key);
        array_pop($parts);

        return implode('-', $parts);
    }
}
