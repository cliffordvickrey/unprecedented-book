<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\ValueObject;

use CliffordVickrey\Book2024\Common\Entity\Entity;

class ImputedCommitteeTotals extends Entity
{
    public float $itemizedActBlue = 0.0;
    public float $unItemizedActBlue = 0.0;
    public float $itemizedWinRed = 0.0;
    public float $unItemizedWinRed = 0.0;
    public float $itemizedBulkUnder200 = 0.0;
    public float $itemizedBulkEqualToOrGreaterTo200 = 0.0;
}
