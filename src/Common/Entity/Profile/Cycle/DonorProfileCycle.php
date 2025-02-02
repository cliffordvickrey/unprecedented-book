<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Profile\Cycle;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\Profile\DonorProfileAmount;
use Webmozart\Assert\Assert;

abstract class DonorProfileCycle extends Entity
{
    public int $cycle = 0;
    public DonorProfileAmount $hybridPac;
    public DonorProfileAmount $nonPresDemocratic;
    public DonorProfileAmount $nonPresRepublican;
    public DonorProfileAmount $nonPresThirdParty;
    public DonorProfileAmount $partyCommittee;
    public DonorProfileAmount $presOtherDemocratic;
    public DonorProfileAmount $presOtherRepublican;
    public DonorProfileAmount $presOtherThirdParty;
    public DonorProfileAmount $superPac;
    public DonorProfileAmount $singleCandidateIndependentExpenditureCommittee;
    public DonorProfileAmount $traditionalPac;

    public function add(string $prop, float $amount): void
    {
        /** @var DonorProfileAmount $amt */
        $amt = $this->{$prop};
        $amt->receipts++;
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
        Assert::isInstanceOf(\DateTimeImmutable::class, $dt);

        return $dt;
    }

    abstract protected function getElectionDayStr(): string;
}
