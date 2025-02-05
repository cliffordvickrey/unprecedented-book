<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Report;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;

class DonorReportRow extends Entity
{
    public DonorCharacteristic $characteristic;
    public DonorReportValue $value;
}
