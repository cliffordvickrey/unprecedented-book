<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity;

#[\Attribute]
final readonly class PropOrder
{
    public function __construct(public int $order)
    {
    }
}
