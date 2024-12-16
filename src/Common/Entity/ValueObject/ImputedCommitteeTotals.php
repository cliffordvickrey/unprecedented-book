<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\ValueObject;

use CliffordVickrey\Book2024\Common\Entity\Combined\Receipt;
use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\FecApi\ScheduleAReceipt;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\ItemizedIndividualReceipt;
use CliffordVickrey\Book2024\Common\Enum\Fec\TransactionType;
use CliffordVickrey\Book2024\Common\Enum\ReceiptSource;
use CliffordVickrey\Book2024\Common\Utilities\MathUtilities;

class ImputedCommitteeTotals extends Entity
{
    public float $candidateContributions = 0.0;
    public float $itemizedActBlue = 0.0;
    public float $unItemizedActBlue = 0.0;
    public float $itemizedWinRed = 0.0;
    public float $unItemizedWinRed = 0.0;
    public float $itemizedBulkUnder200 = 0.0;
    public float $itemizedBulkEqualToOrGreaterTo200 = 0.0;
    public float $otherFederalReceiptsActBlue = 0.0;
    public float $otherFederalReceiptsWinRed = 0.0;
    public float $otherFederalReceiptsBulk = 0.0;
    /** @var array<string, float> */
    public array $apiTotalsByMemo = [];
    /** @var array<string, float> */
    public array $bulkTotalsByMemo = [];
    /** @var array<string, float> */
    public array $apiTotalsByReceiptType = [];
    /** @var array<string, float> */
    public array $bulkTotalsByReceiptType = [];

    public function sumAll(): float
    {
        return MathUtilities::add($this->sumItemized(), $this->sumUnItemized());
    }

    public function sumItemized(): float
    {
        $fromApi = MathUtilities::add($this->itemizedActBlue, $this->itemizedWinRed);
        $fromBulk = MathUtilities::add($this->itemizedBulkUnder200, $this->itemizedBulkEqualToOrGreaterTo200);

        return MathUtilities::add($fromApi, $fromBulk);
    }

    public function sumUnItemized(): float
    {
        return MathUtilities::add($this->unItemizedActBlue, $this->unItemizedWinRed);
    }

    public function addReceipt(Receipt $receipt): void
    {
        $amt = $receipt->amount;
        $originalReceipt = $receipt->getOriginalReceipt();

        $api = false;
        $memo = null;
        $receiptType = null;

        if ($originalReceipt instanceof ScheduleAReceipt) {
            $api = true;
            $memo = $originalReceipt->memo_text;
            $receiptType = $originalReceipt->receipt_type->name ?? 'uncategorized';
        } elseif ($originalReceipt instanceof ItemizedIndividualReceipt) {
            $memo = (string) $originalReceipt->MEMO_TEXT;
            $receiptType = $originalReceipt->TRANSACTION_TP->name ?? 'uncategorized';
        }

        if ('' === $memo) {
            $memo = '[none]';
        }

        if ($api && null !== $memo) {
            if (!isset($this->apiTotalsByMemo[$memo])) {
                $this->apiTotalsByMemo[$memo] = 0.0;
            }

            $this->apiTotalsByMemo[$memo] = MathUtilities::add($this->apiTotalsByMemo[$memo], $amt);
        } elseif (null !== $memo) {
            if (!isset($this->bulkTotalsByMemo[$memo])) {
                $this->bulkTotalsByMemo[$memo] = 0.0;
            }

            $this->bulkTotalsByMemo[$memo] = MathUtilities::add($this->bulkTotalsByMemo[$memo], $amt);
        }

        if ($api && null !== $receiptType) {
            if (!isset($this->apiTotalsByReceiptType[$receiptType])) {
                $this->apiTotalsByReceiptType[$receiptType] = 0.0;
            }

            $this->apiTotalsByReceiptType[$receiptType] = MathUtilities::add(
                $this->apiTotalsByReceiptType[$receiptType],
                $amt
            );
        } elseif (null !== $receiptType) {
            if (!isset($this->bulkTotalsByReceiptType[$receiptType])) {
                $this->bulkTotalsByReceiptType[$receiptType] = 0.0;
            }

            $this->bulkTotalsByReceiptType[$receiptType] = MathUtilities::add(
                $this->bulkTotalsByReceiptType[$receiptType],
                $amt
            );
        }

        $otherFederalAccount = $receipt->transaction_type->isLine7();

        switch (true) {
            case TransactionType::_15C === $receipt->transaction_type:
                // candidate contribution
                $this->candidateContributions = MathUtilities::add($this->candidateContributions, $amt);

                return;
            case ReceiptSource::AB === $receipt->source && $otherFederalAccount:
                // ActBlue other federal account
                $this->otherFederalReceiptsActBlue = MathUtilities::add($this->itemizedActBlue, $amt);

                return;
            case ReceiptSource::WR === $receipt->source && $otherFederalAccount:
                // WinRed other federal account
                $this->otherFederalReceiptsWinRed = MathUtilities::add($this->otherFederalReceiptsWinRed, $amt);

                return;
            case $otherFederalAccount:
                // bulk other federal account
                $this->otherFederalReceiptsBulk = MathUtilities::add($this->otherFederalReceiptsBulk, $amt);

                return;
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
