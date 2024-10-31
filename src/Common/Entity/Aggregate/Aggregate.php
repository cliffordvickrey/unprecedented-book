<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Aggregate;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\PropOrder;

abstract class Aggregate extends Entity
{
    #[PropOrder(0)]
    public string $slug = '';
}
