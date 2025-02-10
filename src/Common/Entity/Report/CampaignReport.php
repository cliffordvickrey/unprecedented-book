<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Report;

use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;
use Webmozart\Assert\Assert;

/**
 * @extends AbstractReport<CampaignReportRow>
 */
class CampaignReport extends AbstractReport
{
    protected function init(): void
    {
    }

    /**
     * @param array<string, ReportValue> $values
     */
    public function addMultiple(array $values): void
    {
        foreach ($values as $dtStr => $value) {
            $date = CastingUtilities::toDateTime($dtStr);
            Assert::notNull($date);
            $this->add($value, $date);
        }
    }

    public function add(ReportValue $value, \DateTimeImmutable $date): void
    {
        if (!$this->hasByDate($date)) {
            $row = new CampaignReportRow();
            $row->date = $date;
            $this->set($row);
        } else {
            $row = $this->getByDate($date);
        }

        $row->value->add($value);
    }

    public function set(CampaignReportRow $row): void
    {
        $this->setByIndex($row->date->format('Y-m-d'), $row);
    }

    public function hasByDate(\DateTimeImmutable $date): bool
    {
        return $this->hasIndex($date->format('Y-m-d'));
    }

    public function getByDate(\DateTimeImmutable $date): CampaignReportRow
    {
        return $this->getByIndex($date->format('Y-m-d'));
    }

    protected function getRowIndex(AbstractReportRow $row): string
    {
        return $row->date->format('Y-m-d');
    }
}
