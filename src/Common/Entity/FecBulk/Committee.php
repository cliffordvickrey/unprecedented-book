<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\FecBulk;

use CliffordVickrey\Book2024\Common\Entity\PropOrder;
use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeDesignation;
use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeFilingFrequency;
use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeInterestGroupCategory;
use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeType;
use CliffordVickrey\Book2024\Common\Enum\Fec\PartyAffiliation;

final class Committee extends FecBulkEntity
{
    #[PropOrder(1)]
    public string $CMTE_ID = ''; // Committee identification
    #[PropOrder(2)]
    public ?string $CMTE_NAME = null; // Committee name
    #[PropOrder(3)]
    public ?string $TRES_NM = null; // Treasurer's name
    #[PropOrder(4)]
    public ?string $CMTE_ST1 = null; // Street one
    #[PropOrder(5)]
    public ?string $CMTE_ST2 = null; // Street two
    #[PropOrder(6)]
    public ?string $CMTE_CITY = null; // City or town
    #[PropOrder(7)]
    public ?string $CMTE_ST = null; // State
    #[PropOrder(8)]
    public ?string $CMTE_ZIP = null; // ZIP code
    #[PropOrder(9)]
    public ?CommitteeDesignation $CMTE_DSGN = null; // Committeee designation
    #[PropOrder(10)]
    public ?CommitteeType $CMTE_TP = null; // Committee type
    #[PropOrder(11)]
    public ?PartyAffiliation $CMTE_PTY_AFFILIATION = null; // Committee party
    #[PropOrder(12)]
    public ?CommitteeFilingFrequency $CMTE_FILING_FREQ = null; // Committee filing frequency
    #[PropOrder(13)]
    public ?CommitteeInterestGroupCategory $ORG_TP = null; // Interest group category
    #[PropOrder(14)]
    public ?string $CONNECTED_ORG_NM = null; // Connected organization's name
    #[PropOrder(15)]
    public ?string $CAND_ID = null; // Candidate identification
}
