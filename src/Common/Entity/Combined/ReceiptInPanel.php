<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Combined;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\PropMeta;

class ReceiptInPanel extends Entity
{
    #[PropMeta(0)]
    public \DateTimeImmutable $date;
    #[PropMeta(1)]
    public string $recipientSlug = '';
    #[PropMeta(2)]
    public bool $itemized = false;
    #[PropMeta(3)]
    public float $amt = 0.0;
    #[PropMeta(4)]
    public string $zip = '';
}
