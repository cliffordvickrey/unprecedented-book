<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Enum\Fec;

enum CommitteeFilingFrequency: string
{
    case A = 'A'; // Administratively terminated
    case D = 'D'; // Debt
    case M = 'M'; // Monthly filer
    case Q = 'Q'; // Quarterly filer
    case T = 'T'; // Terminated
    case W = 'W'; // Waived
}
