<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Report;

use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;
use CliffordVickrey\Book2024\Common\Utilities\MathUtilities;

/**
 * @extends AbstractReport<DonorReportRow>
 *
 * @phpstan-import-type DonorReportRecord from DonorReportRow
 */
class DonorReport extends AbstractReport
{
    public DonorReportValue $totals;

    public function init(): void
    {
        $characteristics = array_values(array_filter(
            DonorCharacteristic::cases(),
            fn (DonorCharacteristic $characteristic) => !$characteristic->isMutuallyExclusiveOrTautologicalWith(
                $this->characteristicA,
                $this->characteristicB,
                $this->campaignType
            )
        ));

        $this->rows = array_map(
            static fn (DonorCharacteristic $characteristic) => DonorReportRow::__set_state([
                'characteristic' => $characteristic,
            ]),
            $characteristics
        );
    }

    /**
     * @return list<DonorReportRecord>
     */
    public function toRecords(): array
    {
        return array_map(static fn (DonorReportRow $row) => $row->toRecord(), $this->rows);
    }

    public function setPercentages(): void
    {
        $this->totals->percent = 1.0;

        array_walk($this->rows, fn (DonorReportRow $row) => $row->value->percent = MathUtilities::divide(
            $row->value->donors,
            $this->totals->donors,
            4
        ));
    }

    /**
     * @param list<DonorCharacteristic> $characteristics
     */
    public function add(ReportValue $value, array $characteristics): void
    {
        $this->totals->add($value);

        array_walk($characteristics, function (DonorCharacteristic $characteristic) use ($value): void {
            if (!$this->hasCharacteristic($characteristic)) {
                return;
            }

            $this->getByCharacteristic($characteristic)->value->add($value);
        });
    }

    public function hasCharacteristic(DonorCharacteristic $characteristic): bool
    {
        return $this->hasIndex($characteristic->value);
    }

    public function getByCharacteristic(DonorCharacteristic $characteristic): DonorReportRow
    {
        return $this->getByIndex($characteristic->value);
    }

    protected function getRowIndex(AbstractReportRow $row): string
    {
        return $row->characteristic->value;
    }
}
