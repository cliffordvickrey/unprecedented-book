<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Enum\Fec;

enum CommitteeType: string
{
    case C = 'C'; // Communication cost
    case D = 'D'; // Delegate committee
    case E = 'E'; // Electioneering communication
    case H = 'H'; // House
    case I = 'I'; // Independent expenditor (person or group)
    case N = 'N'; // PAC - nonqualified
    case O = 'O'; // Independent expenditure-only (Super PACs)
    case P = 'P'; // Presidential
    case Q = 'Q'; // PAC - qualified
    case S = 'S'; // Senate
    case U = 'U'; // Single-candidate independent expenditure
    case V = 'V'; // Hybrid PAC (with Non-Contribution Account) - Nonqualified
    case W = 'W'; // Hybrid PAC (with Non-Contribution Account) - Qualified
    case X = 'X'; // Party - nonqualified
    case Y = 'Y'; // Party - qualified
    case Z = 'Z'; // National party nonfederal account
}
