<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Report;

/**
 * @extends AbstractReport<MapReportRow>
 */
class MapReport extends AbstractReport
{
    public function add(ReportValue $value, string $jurisdiction): void
    {
        if (!$this->hasByJurisdiction($jurisdiction)) {
            $row = new MapReportRow();
            $row->jurisdiction = $jurisdiction;
            $this->set($row);
        } else {
            $row = $this->getByJurisdiction($jurisdiction);
        }

        $row->value->add($value);
    }

    public function hasByJurisdiction(string $jurisdiction): bool
    {
        return $this->hasIndex($jurisdiction);
    }

    public function set(MapReportRow $row): void
    {
        $this->setByIndex($row->jurisdiction, $row);
    }

    public function getByJurisdiction(string $jurisdiction): MapReportRow
    {
        return $this->getByIndex($jurisdiction);
    }

    protected function init(): void
    {
    }

    protected function getRowIndex(AbstractReportRow $row): string
    {
        return $row->jurisdiction;
    }
}
