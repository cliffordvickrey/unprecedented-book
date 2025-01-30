<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Enum;

enum RecipientType: string
{
    case biden = 'biden';
    case biden_or_harris = 'biden_or_harris';
    case harris = 'harris';
    case trump = 'trump';
}
