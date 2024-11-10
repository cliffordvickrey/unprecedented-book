<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\ValueObject;

use CliffordVickrey\Book2024\Common\Entity\Combined\Receipt;
use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Enum\ReceiptSource;
use CliffordVickrey\Book2024\Common\Utilities\MathUtilities;

class ImputedCommitteeTotals extends Entity
{
    public float $itemizedActBlue = 0.0;
    public float $unItemizedActBlue = 0.0;
    public float $itemizedWinRed = 0.0;
    public float $unItemizedWinRed = 0.0;
    public float $itemizedBulkUnder200 = 0.0;
    public float $itemizedBulkEqualToOrGreaterTo200 = 0.0;

    public function addReceipt(Receipt $receipt): void
    {
        $amt = $receipt->amount;

        switch (true) {
            case ReceiptSource::AB === $receipt->source && $receipt->itemized:
                // ActBlue itemized
                $this->itemizedActBlue = MathUtilities::add($this->itemizedActBlue, $amt);

                return;
            case ReceiptSource::AB === $receipt->source:
                // ActBlue un-itemized
                $this->unItemizedActBlue = MathUtilities::add($this->unItemizedActBlue, $amt);

                return;
            case ReceiptSource::WR === $receipt->source && $receipt->itemized:
                // WinRed itemized
                $this->itemizedWinRed = MathUtilities::add($this->itemizedWinRed, $amt);

                return;
            case ReceiptSource::WR === $receipt->source:
                // WinRed un-itemized
                $this->unItemizedWinRed = MathUtilities::add($this->unItemizedWinRed, $amt);

                return;
            case $receipt->isSmall():
                // bulk (< $200)
                $this->itemizedBulkUnder200 = MathUtilities::add($this->itemizedBulkUnder200, $amt);

                return;
        }

        // bulk (>= $200)
        $this->itemizedBulkEqualToOrGreaterTo200 = MathUtilities::add($this->itemizedBulkEqualToOrGreaterTo200, $amt);
    }
}
