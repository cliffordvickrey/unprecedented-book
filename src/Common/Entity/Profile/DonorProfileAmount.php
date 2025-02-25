<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Profile;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Utilities\StringUtilities;

class DonorProfileAmount extends Entity implements \Countable, \Stringable
{
    public float $amount = 0.0;
    /** @var int<0, max> */
    public int $receipts = 0;
    /** @var array<string, bool> */
    public array $slugs = [];

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

    public function count(): int
    {
        return \count($this->slugs);
    }
}
