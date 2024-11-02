<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\ValueObject;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\Candidate;
use CliffordVickrey\Book2024\Common\Enum\Fec\CandidateOffice;
use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeDesignation;
use CliffordVickrey\Book2024\Common\Utilities\StringUtilities;

class CommitteeProperties extends Entity implements \Countable
{
    public string $id = '';
    public string $name = '';
    public ?CommitteeDesignation $committeeDesignation = null;
    public ?string $candidateSlug = null;
    /** @var list<string> */
    public array $candidateSlugs = [];
    public bool $isLeadership = false;
    public ?CandidateOffice $candidateOffice = null;
    public ?string $state = null;
    public ?string $district = null;
    public ?int $year = null;

    public function setCandidate(Candidate $candidate): void
    {
        $this->candidateOffice = $candidate->CAND_OFFICE;
        $this->state = $candidate->CAND_ST;
        $this->district = $candidate->CAND_OFFICE_DISTRICT;
        $this->year = $candidate->CAND_ELECTION_YR;
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function disambiguateSlugs(self $b): array
    {
        $a = $this;
        $slugA = $a->getSlug();
        $slugB = $b->getSlug();

        if ($slugA !== $slugB) {
            return [$slugA, $slugB];
        }

        $designationA = $a->getCommitteeDesignationSlug();
        $designationB = $b->getCommitteeDesignationSlug();

        if ($designationA !== $designationB) {
            return [
                $slugA.'-'.$designationA,
                $slugB.'-'.$designationB,
            ];
        }

        if (
            CandidateOffice::P !== $this->candidateOffice
            && null !== $a->state
            && null !== $b->state
            && $a->state !== $b->state
        ) {
            return [
                $slugA.'-'.$a->state,
                $slugB.'-'.$b->state,
            ];
        }

        if (
            CandidateOffice::H === $this->candidateOffice
            && null !== $a->district
            && null !== $b->district
            && $a->district !== $b->district
        ) {
            return [
                $slugA.'-'.$a->district,
                $slugB.'-'.$b->district,
            ];
        }

        if (null !== $a->year && null !== $b->year && $a->year !== $b->year) {
            return [
                $slugA.\sprintf('-%d', $a->year),
                $slugB.\sprintf('-%d', $b->year),
            ];
        }

        return [$slugA, $slugB];
    }

    public function getSlug(): string
    {
        $candidateSlug = (CommitteeDesignation::J !== $this->committeeDesignation || \count($this) < 2)
            ? $this->candidateSlug
            : null;

        if (null === $candidateSlug) {
            return StringUtilities::slugify($this->name);
        }

        $trailing = '-'.($this->candidateOffice?->getSlug() ?? 'unknown');

        return $candidateSlug.$trailing;
    }

    public function getCommitteeDesignationSlug(): string
    {
        if ($this->isLeadership) {
            return CommitteeDesignation::D->getSlug();
        }

        return $this->committeeDesignation?->getSlug() ?? 'unknown';
    }

    public function count(): int
    {
        return \count($this->candidateSlugs);
    }
}
