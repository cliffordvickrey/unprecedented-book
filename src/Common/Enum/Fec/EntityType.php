<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Enum\Fec;

enum EntityType: string
{
    case CAN = 'CAN'; // Candidate
    case CCM = 'CCM'; // Committee
    case IND = 'IND'; // Individual (a person)
    case ORG = 'ORG'; // Organization (not a committee and not a person)
    case PAC = 'PAC'; // Political Action Committee
    case PTY = 'PTY'; // Party Organization
}
