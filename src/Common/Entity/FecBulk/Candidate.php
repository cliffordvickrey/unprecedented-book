<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\FecBulk;

use CliffordVickrey\Book2024\Common\Entity\PropMeta;
use CliffordVickrey\Book2024\Common\Entity\ValueObject\Jurisdiction;
use CliffordVickrey\Book2024\Common\Enum\Fec\CandidateOffice;
use CliffordVickrey\Book2024\Common\Enum\Fec\CandidateStatus;
use CliffordVickrey\Book2024\Common\Enum\Fec\IncumbentChallengerStatus;
use CliffordVickrey\Book2024\Common\Enum\Fec\PartyAffiliation;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;

final class Candidate extends FecBulkEntity
{
    #[PropMeta(1)]
    public string $CAND_ID = ''; // Candidate identification
    #[PropMeta(2)]
    public ?string $CAND_NAME = null; // Candidate name
    #[PropMeta(3)]
    public ?PartyAffiliation $CAND_PTY_AFFILIATION = null; // Party affiliation
    #[PropMeta(4)]
    public ?int $CAND_ELECTION_YR = null; // Year of election
    #[PropMeta(5)]
    public ?string $CAND_OFFICE_ST = null; // Candidate state
    #[PropMeta(6)]
    public ?CandidateOffice $CAND_OFFICE = null; // Candidate office
    #[PropMeta(7)]
    public ?string $CAND_OFFICE_DISTRICT = null; // Candidate district
    #[PropMeta(8)]
    public ?IncumbentChallengerStatus $CAND_ICI = null; // Incumbent challenger status
    #[PropMeta(9)]
    public ?CandidateStatus $CAND_STATUS = null; // Candidate status
    #[PropMeta(10)]
    public ?string $CAND_PCC = null; // Principal campaign committee
    #[PropMeta(11)]
    public ?string $CAND_ST1 = null; // Mailing address - street
    #[PropMeta(12)]
    public ?string $CAND_ST2 = null; // Mailing address - street2
    #[PropMeta(13)]
    public ?string $CAND_CITY = null; // Mailing address - city
    #[PropMeta(14)]
    public ?string $CAND_ST = null; // Mailing address - state
    #[PropMeta(15)]
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
