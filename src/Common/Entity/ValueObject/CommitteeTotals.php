<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\ValueObject;

use CliffordVickrey\Book2024\Common\Entity\Entity;

class CommitteeTotals extends Entity
{
    public float $candidateContributions = 0.0;
    public float $itemizedReceipts = 0.0;
    public float $unItemizedReceipts = 0.0;
    public float $individualReceipts = 0.0;
    public float $receipts = 0.0;
}
