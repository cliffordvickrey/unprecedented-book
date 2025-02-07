<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Report;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;

/**
 * @phpstan-type DonorReportRecord array{
 *     characteristic: string,
 *     donors: int,
 *     receipts: int,
 *     amount: float,
 *     percent: float
 * }
 */
class DonorReportRow extends Entity
{
    public DonorCharacteristic $characteristic;
    public DonorReportValue $value;

    /**
     * @return DonorReportRecord
     */
    public function toRecord(): array
    {
        return [
            'characteristic' => $this->characteristic->getDescription(),
            'donors' => $this->value->donors,
            'receipts' => $this->value->receipts,
            'amount' => $this->value->amount,
            'percent' => $this->value->percent,
        ];
    }
}
