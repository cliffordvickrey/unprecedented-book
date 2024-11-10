<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Combined;

use CliffordVickrey\Book2024\Common\Entity\Aggregate\CommitteeAggregate;
use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\FecApi\ScheduleAReceipt;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\ItemizedIndividualReceipt;
use CliffordVickrey\Book2024\Common\Enum\Fec\TransactionType;
use CliffordVickrey\Book2024\Common\Enum\ReceiptSource;
use CliffordVickrey\Book2024\Common\Utilities\StringUtilities;

class Receipt extends Entity
{
    public int $id = 0;
    public string $committee_slug = '';
    public ?string $candidate_slug = null;
    public string $fec_committee_id = '';
    public ?string $fec_candidate_id = null;
    public int $donor_id = 0;
    public TransactionType $transaction_type = TransactionType::_15E;
    public \DateTimeImmutable $transaction_date;
    public float $amount = 0.0;
    public string $name = '';
    public string $address = '';
    public string $city = '';
    public string $state = '';
    public string $zip = '';
    public string $occupation = '';
    public string $employer = '';
    public bool $itemized = false;
    public ReceiptSource $source = ReceiptSource::AB;

    public static function fromScheduleAReceipt(ScheduleAReceipt $receipt): self
    {
        $self = new self();

        if (null !== $receipt->receipt_type) {
            $self->transaction_type = $receipt->receipt_type;
        }

        $self->transaction_date = $receipt->contribution_receipt_date;
        $self->amount = $receipt->contribution_receipt_amount;
        $self->name = $receipt->contributor_name;
        $self->address = $receipt->contributor_street_1;
        $self->city = $receipt->contributor_city;
        $self->zip = $receipt->contributor_zip;
        $self->occupation = $receipt->contributor_occupation;
        $self->employer = $receipt->contributor_employer;

        return $self;
    }

    public static function fromItemizedReceipt(ItemizedIndividualReceipt $receipt): self
    {
        $self = new self();
        $self->fec_committee_id = $receipt->CMTE_ID;

        if (null !== $receipt->TRANSACTION_TP) {
            $self->transaction_type = $receipt->TRANSACTION_TP;
        }

        if (null !== $receipt->TRANSACTION_DT) {
            $self->transaction_date = $receipt->TRANSACTION_DT;
        }

        $self->amount = (float) $receipt->TRANSACTION_AMT;
        $self->name = (string) $receipt->NAME;
        $self->city = (string) $receipt->CITY;
        $self->state = (string) $receipt->STATE;
        $self->zip = (string) $receipt->ZIP_CODE;
        $self->occupation = (string) $receipt->OCCUPATION;
        $self->employer = (string) $receipt->EMPLOYER;
        $self->itemized = true;
        $self->source = ReceiptSource::BK;

        return $self;
    }

    public function setCommitteeAggregate(CommitteeAggregate $committeeAggregate): void
    {
        $this->committee_slug = $committeeAggregate->slug;
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

    public function isSmall(): bool
    {
        return $this->amount < 200.0;
    }

    public function getZip5(): string
    {
        return StringUtilities::parseZip($this->zip)['zip5'];
    }

    public function getSurname(): string
    {
        $nameParts = explode(',', $this->name, 2);

        return array_shift($nameParts);
    }

    public function getHash(): string
    {
        return md5(serialize([
            $this->fec_committee_id,
            $this->transaction_date->format('Y-m-d'),
            $this->amount,
            $this->getSurname(),
            $this->getZip5(),
        ]));
    }
}
