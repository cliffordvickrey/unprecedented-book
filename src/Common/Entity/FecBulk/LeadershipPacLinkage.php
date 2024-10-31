<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\FecBulk;

use CliffordVickrey\Book2024\Common\Entity\PropOrder;
use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeDesignation;
use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeType;

final class LeadershipPacLinkage extends FecBulkEntity
{
    #[PropOrder(1)]
    public string $CAND_ID = ''; // Candidate identification
    #[PropOrder(2)]
    public string $CAND_ELECTION_YR = ''; // Candidate election year
    #[PropOrder(3)]
    public string $FEC_ELECTION_YR = ''; // FEC election year
    #[PropOrder(4)]
    public ?string $CMTE_ID = null; // Committee identification
    #[PropOrder(5)]
    public ?CommitteeType $CMTE_TP = null; // Committee type
    #[PropOrder(6)]
    public ?CommitteeDesignation $CMTE_DSGN = null; // Committee designation
    #[PropOrder(7)]
    public int $LINKAGE_ID = 0; // Linkage ID
}
