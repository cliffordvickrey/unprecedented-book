<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Aggregate;

use CliffordVickrey\Book2024\Common\Entity\FecBulk\Candidate;
use CliffordVickrey\Book2024\Common\Entity\PropMeta;
use CliffordVickrey\Book2024\Common\Entity\ValueObject\Jurisdiction;
use CliffordVickrey\Book2024\Common\Enum\Fec\CandidateOffice;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type IndexedCandidateInfo array<int, non-empty-array<string, Candidate>>
 */
class CandidateAggregate extends Aggregate
{
    #[PropMeta(1)]
    public string $name = '';
    /** @var list<Candidate> */
    #[PropMeta(2)]
    public array $info = [];
    /** @var array<string, bool> */
    #[PropMeta(3)]
    public array $democraticNominations = [];
    /** @var array<string, bool> */
    #[PropMeta(4)]
    public array $republicanNominations = [];
    /** @var IndexedCandidateInfo|null */
    private ?array $indexedInfo = null;

    public function ranForPresident(?int $startYear = null, ?int $endYear = null): bool
    {
        $presidentialRuns = array_filter(
            $this->info,
            static function (Candidate $candidate) use ($startYear, $endYear): bool {
                if (CandidateOffice::P !== $candidate->CAND_OFFICE) {
                    return false;
                }

                $electionYear = (int) $candidate->CAND_ELECTION_YR;

                if (null !== $startYear && $electionYear < $startYear) {
                    return false;
                }

                if (null !== $endYear && $electionYear > $endYear) {
                    return false;
                }

                return true;
            });

        return \count($presidentialRuns) > 0;
    }

    public function getInfoByYearAndJurisdiction(int $year, Jurisdiction $jurisdiction): ?Candidate
    {
        $candidates = $this->getIndexedCandidateInfo()[$year] ?? [];

        $candidatesInJurisdiction = array_filter(
            $candidates,
            static fn (Candidate $candidate) => (string) $jurisdiction === (string) $candidate->getJurisdiction()
        );

        if (0 === \count($candidatesInJurisdiction)) {
            return null;
        }

        return $candidatesInJurisdiction[array_key_first($candidatesInJurisdiction)];
    }

    /**
     * @return IndexedCandidateInfo
     */
    private function getIndexedCandidateInfo(?CandidateOffice $office = null): array
    {
        $this->indexedInfo ??= $this->buildIndexedCandidateInfo();

        $info = $this->indexedInfo;

        if (null === $office) {
            return $info;
        }

        $filteredInfo = [];

        foreach ($info as $cycle => $candidates) {
            $candidates = array_filter(
                $candidates,
                static fn (Candidate $candidate) => $candidate->CAND_OFFICE === $office
            );

            if (0 !== \count($candidates)) {
                $filteredInfo[$cycle] = $candidates;
            }
        }

        return $filteredInfo;
    }

    /**
     * @return IndexedCandidateInfo
     */
    private function buildIndexedCandidateInfo(): array
    {
        Assert::notEmpty($this->info, \sprintf('Candidate (%s) has no FEC info associated with them', $this->slug));

        // @phpstan-ignore-next-line
        return array_reduce($this->info, static function (array $carry, Candidate $info): array {
            /** @var array<int, array<string, Candidate>> $carry */
            if (!isset($carry[$info->file_id])) {
                $carry[$info->file_id] = [$info->CAND_ID => $info];

                return $carry;
            }

            $carry[$info->file_id][$info->CAND_ID] = $info;

            return $carry;
        }, []);
    }

    public function getInfo(
        ?int $year = null,
        ?string $candidateId = null,
        ?CandidateOffice $office = null,
        bool $fallback = true,
    ): ?Candidate {
        if (0 === \count($this->info)) {
            return null;
        }

        $info = $this->doGetInfo($year, $candidateId, $office);

        if ($info || !$fallback) {
            return $info;
        }

        if (null !== $year) {
            return $this->getInfo(candidateId: $candidateId, office: $office);
        }

        if (null !== $candidateId) {
            return $this->getInfo($year, office: $office);
        }

        if (null !== $office) {
            return $this->getInfo($candidateId, $year);
        }

        return $this->info[array_key_last($this->info)];
    }

    private function doGetInfo(?int $year, ?string $candidateId, ?CandidateOffice $office): ?Candidate
    {
        $indexed = $this->getIndexedCandidateInfo($office);

        if (null === $year && null === $candidateId) {
            $candidateId = array_key_first($indexed[array_key_last($indexed)]);
        } elseif (null === $year) {
            $yearsMatched = array_filter($indexed, static fn ($candidates) => isset($candidates[$candidateId]));

            if (0 === \count($yearsMatched)) {
                return null;
            }

            $year = array_key_last($yearsMatched);
        }

        $infos = $indexed[$year] ?? null;

        if (empty($infos)) {
            return null;
        }

        if (null === $candidateId) {
            return $infos[array_key_first($infos)];
        }

        return $infos[$candidateId] ?? null;
    }

    public function isNominee(int $year, Jurisdiction $jurisdiction, ?bool $democrat = null): bool
    {
        $key = \sprintf('%d%s', $year, $jurisdiction);
        $isNominee = $this->democraticNominations[$key] ?? false;

        if (false === $democrat || (!$isNominee && null === $democrat)) {
            $isNominee = $this->republicanNominations[$key] ?? false;
        }

        return $isNominee;
    }
}
