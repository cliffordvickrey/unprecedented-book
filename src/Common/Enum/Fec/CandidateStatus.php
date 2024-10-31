<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Enum\Fec;

enum CandidateStatus: string
{
    case C = 'C'; // Statutory candidate
    case F = 'F'; // Statutory candidate for future election
    case N = 'N'; // Not yet a statutory candidate
    case P = 'P'; // Statutory candidate in prior cycle
}
