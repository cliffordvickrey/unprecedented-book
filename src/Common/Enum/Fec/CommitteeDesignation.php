<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Enum\Fec;

enum CommitteeDesignation: string
{
    case A = 'A'; // Authorized by a candidate
    case B = 'B'; // Lobbyist/Registrant PAC
    case D = 'D'; // Leadership PAC
    case J = 'J'; // Joint fundraiser
    case P = 'P'; // Principal campaign committee
    case U = 'U'; // Unauthorized
}
