<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Combined;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\PropMeta;
use Webmozart\Assert\Assert;

class ReceiptInPanel extends Entity
{
    #[PropMeta(0)]
    public \DateTimeImmutable $date;
    #[PropMeta(1)]
    public string $recipientSlug = '';
    #[PropMeta(2)]
    public bool $itemized = false;
    #[PropMeta(3)]
    public float $amount = 0.0;
    #[PropMeta(4)]
    public string $zip = '';

    public function getCycle(): int
    {
        $year = $this->date->format('Y');
        Assert::numeric($year);
        $year = (int)$year;
        return ($year % 2 === 0) ? $year : ($year + 1);
    }

    public static function fromReceipt(Receipt $receipt): self
    {
        $self = new self();
        $self->date = $receipt->transaction_date;
        $self->recipientSlug = $receipt->committee_slug;
        $self->itemized = $receipt->itemized;
        $self->amount = $receipt->amount;
        $self->zip = $receipt->zip;

        return $self;
    }
}
