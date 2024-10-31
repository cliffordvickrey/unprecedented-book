<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Enum\Fec;

enum IncumbentChallengerStatus: string
{
    case C = 'C'; // Challenger
    case I = 'I'; // Incumbent
    case O = 'O'; // Open Seat
}
