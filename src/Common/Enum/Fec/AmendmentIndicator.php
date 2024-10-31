<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Enum\Fec;

enum AmendmentIndicator: string
{
    case N = 'N'; // new
    case A = 'A'; // amendment
    case T = 'T'; // termination
}
