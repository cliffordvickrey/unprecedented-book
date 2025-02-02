<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Profile;

use CliffordVickrey\Book2024\Common\Entity\Entity;

class DonorProfileAmount extends Entity
{
    public float $amount = 0.0;
    public int $receipts = 0;
}
