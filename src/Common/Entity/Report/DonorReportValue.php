<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Report;

use CliffordVickrey\Book2024\Common\Entity\Entity;

class DonorReportValue extends Entity
{
    public int $donors = 0;
    public int $receipts = 0;
    public float $amount = 0.0;
    public float $percent = 0.0;
}
