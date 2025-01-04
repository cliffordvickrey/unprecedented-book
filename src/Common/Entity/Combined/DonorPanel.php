<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Combined;

use CliffordVickrey\Book2024\Common\Entity\Entity;

class DonorPanel extends Entity
{
    public Donor $donor;
    /** @var list<ReceiptInPanel> */
    public array $receipts = [];

    /**
     * @return int<-1, 1>
     */
    private static function receiptSorter(ReceiptInPanel $a, ReceiptInPanel $b): int
    {
        $arrA = $a->toArray(true);
        $arrB = $b->toArray(true);

        foreach ($arrA as $prop => $valueA) {
            $valueB = $arrB[$prop];

            $cmp = $valueA <=> $valueB;

            if ($cmp) {
                return $cmp;
            }
        }

        return 0;
    }

    public function sortReceipts(): void
    {
        usort($this->receipts, self::receiptSorter(...));
    }
}
