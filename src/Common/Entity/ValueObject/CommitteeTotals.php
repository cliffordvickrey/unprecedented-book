<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\ValueObject;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Utilities\MathUtilities;

class CommitteeTotals extends Entity
{
    public float $itemizedReceipts = 0.0;
    public float $unItemizedReceipts = 0.0;
    public float $receipts = 0.0;

    public function getTotalIndividualReceipts(): float
    {
        return MathUtilities::add($this->itemizedReceipts, $this->unItemizedReceipts);
    }
}
