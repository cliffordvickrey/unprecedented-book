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
            self::donors => 'Unique Donors',
            self::receipts => 'Receipts',
            self::amount => 'Receipt Amounts',
        };
    }
}
