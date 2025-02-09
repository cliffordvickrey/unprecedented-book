<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\DTO;

enum GraphType: string
{
    case donors = 'donors';
    case receipts = 'receipts';
    case amount = 'amount';

    public function isDollarAmount(): bool
    {
        return self::amount === $this;
    }

    public function getTitle(): string
    {
        return match ($this) {
            self::donors => 'Daily Unique Donors',
            self::receipts => 'Daily Receipts',
            self::amount => 'Daily Receipt Amounts',
        };
    }
}
