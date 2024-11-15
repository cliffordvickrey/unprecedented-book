<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\FecBulk;

use CliffordVickrey\Book2024\Common\Entity\PropMeta;
use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeDesignation;
use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeFilingFrequency;
use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeInterestGroupCategory;
use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeType;
use CliffordVickrey\Book2024\Common\Enum\Fec\PartyAffiliation;

final class Committee extends FecBulkEntity
{
    #[PropMeta(1)]
    public string $CMTE_ID = ''; // Committee identification
    #[PropMeta(2)]
    public ?string $CMTE_NAME = null; // Committee name
    #[PropMeta(3)]
    public ?string $TRES_NM = null; // Treasurer's name
    #[PropMeta(4)]
    public ?string $CMTE_ST1 = null; // Street one
    #[PropMeta(5)]
    public ?string $CMTE_ST2 = null; // Street two
    #[PropMeta(6)]
    public ?string $CMTE_CITY = null; // City or town
    #[PropMeta(7)]
    public ?string $CMTE_ST = null; // State
    #[PropMeta(8)]
    public ?string $CMTE_ZIP = null; // ZIP code
    #[PropMeta(9)]
    public ?CommitteeDesignation $CMTE_DSGN = null; // Committeee designation
    #[PropMeta(10)]
    public ?CommitteeType $CMTE_TP = null; // Committee type
    #[PropMeta(11)]
    public ?PartyAffiliation $CMTE_PTY_AFFILIATION = null; // Committee party
    #[PropMeta(12)]
    public ?CommitteeFilingFrequency $CMTE_FILING_FREQ = null; // Committee filing frequency
    #[PropMeta(13)]
    public ?CommitteeInterestGroupCategory $ORG_TP = null; // Interest group category
    #[PropMeta(14)]
    public ?string $CONNECTED_ORG_NM = null; // Connected organization's name
    #[PropMeta(15)]
    public ?string $CAND_ID = null; // Candidate identification
}
