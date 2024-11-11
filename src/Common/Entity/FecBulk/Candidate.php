<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\FecBulk;

use CliffordVickrey\Book2024\Common\Entity\PropOrder;
use CliffordVickrey\Book2024\Common\Entity\ValueObject\Jurisdiction;
use CliffordVickrey\Book2024\Common\Enum\Fec\CandidateOffice;
use CliffordVickrey\Book2024\Common\Enum\Fec\CandidateStatus;
use CliffordVickrey\Book2024\Common\Enum\Fec\IncumbentChallengerStatus;
use CliffordVickrey\Book2024\Common\Enum\Fec\PartyAffiliation;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;

final class Candidate extends FecBulkEntity
{
    #[PropOrder(1)]
    public string $CAND_ID = ''; // Candidate identification
    #[PropOrder(2)]
    public ?string $CAND_NAME = null; // Candidate name
    #[PropOrder(3)]
    public ?PartyAffiliation $CAND_PTY_AFFILIATION = null; // Party affiliation
    #[PropOrder(4)]
    public ?int $CAND_ELECTION_YR = null; // Year of election
    #[PropOrder(5)]
    public ?string $CAND_OFFICE_ST = null; // Candidate state
    #[PropOrder(6)]
    public ?CandidateOffice $CAND_OFFICE = null; // Candidate office
    #[PropOrder(7)]
    public ?string $CAND_OFFICE_DISTRICT = null; // Candidate district
    #[PropOrder(8)]
    public ?IncumbentChallengerStatus $CAND_ICI = null; // Incumbent challenger status
    #[PropOrder(9)]
    public ?CandidateStatus $CAND_STATUS = null; // Candidate status
    #[PropOrder(10)]
    public ?string $CAND_PCC = null; // Principal campaign committee
    #[PropOrder(11)]
    public ?string $CAND_ST1 = null; // Mailing address - street
    #[PropOrder(12)]
    public ?string $CAND_ST2 = null; // Mailing address - street2
    #[PropOrder(13)]
    public ?string $CAND_CITY = null; // Mailing address - city
    #[PropOrder(14)]
    public ?string $CAND_ST = null; // Mailing address - state
    #[PropOrder(15)]
    public ?string $CAND_ZIP = null; // Mailing address - ZIP code

    public function getJurisdiction(): ?Jurisdiction
    {
        if (null === $this->CAND_OFFICE) {
            return null;
        }

        if (CandidateOffice::P === $this->CAND_OFFICE) {
            return new Jurisdiction('US');
        }

        if (null === $this->CAND_OFFICE_ST) {
            return null;
        }

        $state = $this->CAND_OFFICE_ST;

        if (CandidateOffice::S === $this->CAND_OFFICE) {
            return new Jurisdiction($state);
        }

        $district = $this->CAND_OFFICE_DISTRICT;

        if (!is_numeric($district)) {
            $district = 0;
        } else {
            $district = CastingUtilities::toInt($district);
        }

        return new Jurisdiction($state, $district);
    }
}
