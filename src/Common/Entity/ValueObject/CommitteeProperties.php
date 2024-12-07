<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\ValueObject;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\Candidate;
use CliffordVickrey\Book2024\Common\Enum\Fec\CandidateOffice;
use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeDesignation;
use CliffordVickrey\Book2024\Common\Utilities\StringUtilities;

/**
 * @phpstan-type Diff array{
 *     office?: string,
 *     designation?: string,
 *     state?: string,
 *     district?: string,
 *     year?: string
 * }
 */
class CommitteeProperties extends Entity implements \Countable
{
    public const string PART_OFFICE = 'office';
    public const string PART_DESIGNATION = 'designation';
    public const string PART_STATE = 'state';
    public const string PART_DISTRICT = 'district';
    public const string PART_YEAR = 'year';

    /**
     * Committees with hardcoded slugs (for ease of analysis).
     *
     * @var array<string, string>
     */
    private static array $hardCodedSlugs = [
        'C00867937' => 'donald_trump-47_committee',
        'C00580100' => 'donald_trump-make_america_great_again_pac',
        'C00618371' => 'donald_trump-trump_make_america_great_again_committee',
        'C00873893' => 'donald_trump-trump_national_committee',
        'C00770941' => 'donald_trump-trump_save_america',
        'C00618389' => 'donald_trump-trump_victory',
        'C00703975' => 'kamala_harris-pres-2024',
        'C00838912' => 'kamala_harris-harris_action_fund',
        'C00744946' => 'kamala_harris-harris_victory_fund',
    ];

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
        $this->state = $candidate->CAND_OFFICE_ST;
        $this->district = $candidate->CAND_OFFICE_DISTRICT;
        $this->year = $candidate->CAND_ELECTION_YR;
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function performQuickAndDirtyDisambiguation(self $b): array
    {
        $diffA = $this->diff($b);
        $diffB = $b->diff($b);

        $trailingA = '';
        $trailingB = '';

        foreach ($diffA as $k => $valueA) {
            if (self::PART_OFFICE === $k) {
                break;
            }

            $valueB = $diffB[$k] ?? '';
            $trailingA = "-$valueA";
            $trailingB = "-$valueB";
            break;
        }

        return [$this->getSlug().$trailingA, $b->getSlug().$trailingB];
    }

    /**
     * @return Diff
     */
    public function diff(self $b): array
    {
        $a = $this;

        $diff = [];

        $slugA = $a->getSlug();
        $slugB = $b->getSlug();

        if ($slugA !== $slugB) {
            $diff[self::PART_OFFICE] = $a->getOfficeSlug();
        }

        $designationA = $a->getCommitteeDesignationSlug();
        $designationB = $b->getCommitteeDesignationSlug();

        if ($designationA !== $designationB) {
            $diff[self::PART_DESIGNATION] = $designationA;
        }

        if (
            CandidateOffice::P !== $a->candidateOffice
            && null !== $a->state
            && null !== $b->state
            && $a->state !== $b->state
        ) {
            $diff[self::PART_STATE] = $a->state;
        }

        if (
            CandidateOffice::H === $a->candidateOffice
            && null !== $a->district
            && null !== $b->district
            && $a->district !== $b->district
        ) {
            $diff[self::PART_DISTRICT] = $a->district;
        }

        if (null !== $a->year && null !== $b->year && $a->year !== $b->year) {
            $diff[self::PART_YEAR] = \sprintf('%d', $a->year);
        }

        return array_map(\strtolower(...), $diff);
    }

    public function getSlug(): string
    {
        if (isset(self::$hardCodedSlugs[$this->id])) {
            return self::$hardCodedSlugs[$this->id];
        }

        $candidateSlug = (CommitteeDesignation::J !== $this->committeeDesignation || \count($this) < 2)
            ? $this->candidateSlug
            : null;

        if (null === $candidateSlug) {
            return StringUtilities::slugify($this->name, maxLength: 100);
        }

        $officeSlug = $this->getOfficeSlug();
        $trailing = '';

        if ('' !== $officeSlug) {
            $trailing = "-$officeSlug";
        }

        return $candidateSlug.$trailing;
    }

    public function getOfficeSlug(): string
    {
        if ($this->isLeadership) {
            return 'leader';
        }

        return $this->candidateOffice?->getSlug() ?? 'unknown';
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
