<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Profile;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Utilities\StringUtilities;

class DonorProfileAmount extends Entity implements \Stringable
{
    public float $amount = 0.0;
    public int $receipts = 0;

    public function __toString(): string
    {
        return \sprintf(
            '%s%s (%s contribution%s)',
            '$',
            StringUtilities::numberFormat($this->amount, 2),
            StringUtilities::numberFormat($this->receipts),
            1 === $this->receipts ? '' : 's'
        );
    }
}
