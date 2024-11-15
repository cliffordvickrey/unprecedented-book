<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\FecBulk;

use CliffordVickrey\Book2024\Common\Entity\PropMeta;
use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeDesignation;
use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeType;

final class CandidateCommitteeLinkage extends FecBulkEntity
{
    #[PropMeta(1)]
    public string $CAND_ID = ''; // Candidate identification
    #[PropMeta(2)]
    public int $CAND_ELECTION_YR = 0; // Candidate election year
    #[PropMeta(3)]
    public int $FEC_ELECTION_YR = 0; // FEC election year
    #[PropMeta(4)]
    public ?string $CMTE_ID = null; // Committee identification
    #[PropMeta(5)]
    public ?CommitteeType $CMTE_TP = null; // Committee type
    #[PropMeta(6)]
    public ?CommitteeDesignation $CMTE_DSGN = null; // Committee designation
    #[PropMeta(7)]
    public int $LINKAGE_ID = 0; // Linkage ID
}
