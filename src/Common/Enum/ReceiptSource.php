<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Enum;

enum ReceiptSource: string
{
    case AB = 'AB'; // ActBlue earmarked contribution
    case BK = 'BK'; // FEC bulk file
    case WR = 'WR'; // WinRed earmarked contributions
}
