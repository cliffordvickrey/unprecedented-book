<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Enum;

enum PartyType: string
{
    case democrat = 'democrat';
    case republican = 'republican';
    case other = 'other';
}