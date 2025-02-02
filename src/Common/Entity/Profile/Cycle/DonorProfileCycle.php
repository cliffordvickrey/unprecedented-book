<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Profile\Cycle;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\Profile\DonorProfileAmount;
use CliffordVickrey\Book2024\Common\Enum\PartyType;
use Webmozart\Assert\Assert;

abstract class DonorProfileCycle extends Entity
{
    public int $cycle = 0;
    #[RecipientAttribute(party: PartyType::democratic, description: 'Democratic House candidate(s)')]
    public DonorProfileAmount $houseDemocratic;
    #[RecipientAttribute(party: PartyType::republican, description: 'Republican House candidate(s)')]
    public DonorProfileAmount $houseRepublican;
    #[RecipientAttribute(party: PartyType::republican, description: 'Third Party/Independent House candidate(s)')]
    public DonorProfileAmount $houseThirdParty;
    #[RecipientAttribute(description: 'Hybrid (Carey) PAC(s)')]
    public DonorProfileAmount $hybridPac;
    #[RecipientAttribute(description: 'Party Committee(s)')]
    public DonorProfileAmount $partyCommittee;
    #[RecipientAttribute(party: PartyType::democratic, description: 'Other Democratic Presidential candidate(s)')]
    public DonorProfileAmount $presOtherDemocratic;
    #[RecipientAttribute(party: PartyType::republican, description: 'Other Republican Presidential candidate(s)')]
    public DonorProfileAmount $presOtherRepublican;
    #[RecipientAttribute(
        party: PartyType::republican,
        description: 'Other Third Party/Independent Presidential candidate(s)')
    ]
    public DonorProfileAmount $presOtherThirdParty;
    #[RecipientAttribute(party: PartyType::democratic, description: 'Democratic Senate candidate(s)')]
    public DonorProfileAmount $senateDemocratic;
    #[RecipientAttribute(party: PartyType::democratic, description: 'Republican Senate candidate(s)')]
    public DonorProfileAmount $senateRepublican;
    #[RecipientAttribute(party: PartyType::democratic, description: 'Third Party/Independent Senate candidate(s)')]
    public DonorProfileAmount $senateThirdParty;
    #[RecipientAttribute(description: 'Super PAC(s)')]
    public DonorProfileAmount $superPac;
    #[RecipientAttribute(description: 'Single Candidate Independent Expenditure Committee(s)')]
    public DonorProfileAmount $singleCandidateIndependentExpenditureCommittee;
    #[RecipientAttribute(description: 'Traditional PAC(s)')]
    public DonorProfileAmount $traditionalPac;

    public function add(string $prop, float $amount): void
    {
        /** @var DonorProfileAmount $amt */
        $amt = $this->{$prop};
        ++$amt->receipts;
        $amt->amount += $amount;
    }

    public function getElectionDate(): \DateTimeImmutable
    {
        /** @phpstan-var \DateTimeImmutable|null $dt */
        static $dt = null;

        if (null !== $dt) {
            return $dt;
        }

        $dt = \DateTimeImmutable::createFromFormat('Y-m-d', $this->getElectionDayStr()) ?: null;
        $dt = $dt?->setTime(0, 0);
        Assert::isInstanceOf($dt, \DateTimeImmutable::class);

        return $dt;
    }

    abstract protected function getElectionDayStr(): string;
}
