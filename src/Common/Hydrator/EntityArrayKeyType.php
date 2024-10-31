<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Hydrator;

enum EntityArrayKeyType
{
    case int;
    case intAssociative;
    case string;
}
