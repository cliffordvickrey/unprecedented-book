<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Enum\Fec;

enum CommitteeInterestGroupCategory: string
{
    case C = 'C'; // Corporation
    case L = 'L'; // Labor organization
    case M = 'M'; // Membership organization
    case T = 'T'; // Trade Association
    case V = 'V'; // Cooperative
    case W = 'W'; // Corporation without capital stock
}
