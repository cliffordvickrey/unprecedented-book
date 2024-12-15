<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Combined;

use CliffordVickrey\Book2024\Common\Entity\Aggregate\CommitteeAggregate;
use CliffordVickrey\Book2024\Common\Entity\FecApi\ScheduleAReceipt;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\ItemizedIndividualReceipt;
use CliffordVickrey\Book2024\Common\Entity\PropMeta;
use CliffordVickrey\Book2024\Common\Enum\Fec\TransactionType;
use CliffordVickrey\Book2024\Common\Enum\ReceiptSource;
use CliffordVickrey\Book2024\Common\Utilities\StringUtilities;

class Receipt extends Donor
{
    private const float MAX_UN_ITEMIZED_EXPENDITURE = 200.0;
    private const string ACT_BLUE = 'C00401224';
    private const string WIN_RED = 'C00694323';

    #[PropMeta(1)]
    public string $committee_slug = '';
    #[PropMeta(2)]
    public ?string $candidate_slug = null;
    #[PropMeta(3)]
    public string $fec_committee_id = '';
    #[PropMeta(4)]
    public ?string $fec_candidate_id = null;
    #[PropMeta(5)]
    public int $donor_id = 0;
    #[PropMeta(6)]
    public TransactionType $transaction_type = TransactionType::_15E;
    #[PropMeta(7)]
    public \DateTimeImmutable $transaction_date;
    #[PropMeta(8)]
    public float $amount = 0.0;
    #[PropMeta(9)]
    public bool $itemized = false;
    #[PropMeta(10)]
    public bool $escrow = false;
    #[PropMeta(11)]
    public ReceiptSource $source = ReceiptSource::AB;
    private ScheduleAReceipt|ItemizedIndividualReceipt|null $originalReceipt = null;

    public static function fromScheduleAReceipt(ScheduleAReceipt $receipt): self
    {
        $self = new self();
        $self->originalReceipt = $receipt;
        $self->transaction_date = $receipt->contribution_receipt_date;
        $self->amount = $receipt->contribution_receipt_amount;
        $self->name = strtoupper(trim($receipt->contributor_name));
        $self->address = strtoupper(trim($receipt->contributor_street_1));
        $self->city = strtoupper(trim($receipt->contributor_city));
        $self->state = strtoupper(trim($receipt->contributor_state));
        $self->zip = strtoupper(trim($receipt->contributor_zip));
        $self->occupation = strtoupper(trim($receipt->contributor_occupation));
        $self->employer = strtoupper(trim($receipt->contributor_employer));
        $self->escrow = str_starts_with(trim($receipt->memo_text), 'EARMARKED FOR DEMOCRATIC NOMINEE FOR');

        $self->normalize();

        return $self;
    }

    public static function fromItemizedReceipt(ItemizedIndividualReceipt $receipt): self
    {
        $self = new self();
        $self->originalReceipt = $receipt;
        $self->fec_committee_id = $receipt->CMTE_ID;

        if (null !== $receipt->TRANSACTION_TP) {
            $self->transaction_type = $receipt->TRANSACTION_TP;
        }

        if (null !== $receipt->TRANSACTION_DT) {
            $self->transaction_date = $receipt->TRANSACTION_DT;
        }

        $self->amount = (float) $receipt->TRANSACTION_AMT;
        $self->source = ReceiptSource::BK;

        $self->name = strtoupper(trim((string) $receipt->NAME));
        $self->city = strtoupper(trim((string) $receipt->CITY));
        $self->state = strtoupper(trim((string) $receipt->STATE));
        $self->zip = strtoupper(trim((string) $receipt->ZIP_CODE));
        $self->occupation = strtoupper(trim((string) $receipt->OCCUPATION));
        $self->employer = strtoupper(trim((string) $receipt->EMPLOYER));
        $self->itemized = true;

        $self->normalize();

        return $self;
    }

    public function couldHaveBeenDisbursedThroughConduit(): bool
    {
        return (
            ReceiptSource::BK !== $this->source
            || $this->transaction_type->isEarmarkedIndividualContribution()
            || (
                TransactionType::_15 === $this->transaction_type
                && (
                    self::ACT_BLUE === $this->fec_committee_id
                    || self::WIN_RED === $this->fec_committee_id
                )
            )
        ) && $this->isSmall();
    }

    public function isSmall(): bool
    {
        return $this->amount <= self::MAX_UN_ITEMIZED_EXPENDITURE;
    }

    public function getOriginalReceipt(): ScheduleAReceipt|ItemizedIndividualReceipt|null
    {
        return $this->originalReceipt;
    }

    public function toDonor(): Donor
    {
        $donor = new Donor();
        $donor->name = $this->name;
        $donor->address = $this->address;
        $donor->city = $this->city;
        $donor->state = $this->state;
        $donor->zip = $this->zip;
        $donor->occupation = $this->occupation;
        $donor->employer = $this->employer;

        return $donor;
    }

    public function setCommitteeAggregate(CommitteeAggregate $committeeAggregate): void
    {
        /** @phpstan-var array<string, true> $committeesSharedByBidenAndHarris */
        static $committeesSharedByBidenAndHarris = [
            'C00703975' => true, // (HARRIS|BIDEN) FOR PRESIDENT
            'C00744946' => true, // (HARRIS|BIDEN) VICTORY FUND
            'C00838912' => true, // (HARRIS|BIDEN) ACTION FUND
        ];

        $this->committee_slug = $committeeAggregate->slug;
        $this->fec_committee_id = $committeeAggregate->id;

        $committeeIsSharedByBidenAndHarris = isset($committeesSharedByBidenAndHarris[$this->fec_committee_id]);

        if ($committeeIsSharedByBidenAndHarris && $this->transaction_date->format('Y-m-d') > '2024-07-20') {
            $this->candidate_slug = 'kamala_harris';
            $this->fec_candidate_id = 'P00009423';

            return;
        } elseif ($committeeIsSharedByBidenAndHarris) {
            $this->candidate_slug = 'joe_biden';
            $this->fec_candidate_id = 'P80000722';

            return;
        }

        $this->candidate_slug = $committeeAggregate->getCandidateSlug();
        $this->fec_candidate_id = $committeeAggregate->getCandidateIdByYear($this->getElectionCycle());
    }

    public function getElectionCycle(): int
    {
        /** @var numeric-string $yearStr */
        $yearStr = $this->transaction_date->format('Y');
        $year = (int) $yearStr;

        if (0 === $year % 2) {
            return $year;
        }

        return $year + 1;
    }

    public function getReceiptHash(): string
    {
        return StringUtilities::md5([
            $this->fec_committee_id,
            $this->transaction_date->format('Y-m-d'),
            $this->getAmountFloor(),
            $this->getNormalizedSurname(),
            $this->getZip5(),
        ]);
    }

    public function getAmountFloor(): int
    {
        return (int) floor($this->amount);
    }
}
