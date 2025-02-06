<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Report;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\Profile\DonorProfileAmount;
use CliffordVickrey\Book2024\Common\Utilities\MathUtilities;

class DonorReportValue extends Entity
{
    public int $donors = 0;
    public int $receipts = 0;
    public float $amount = 0.0;
    public float $percent = 0.0;

    public static function fromDonorProfileAmount(DonorProfileAmount $amount): self
    {
        $self = new self();
        $self->donors = 1;
        $self->receipts = $amount->receipts;
        $self->amount = max($amount->amount, 0.0);

        return $self;
    }

    public function add(self $value): void
    {
        $this->donors += $value->donors;
        $this->receipts += $value->receipts;
        $this->amount = MathUtilities::add($this->amount, $value->amount);
    }
}
