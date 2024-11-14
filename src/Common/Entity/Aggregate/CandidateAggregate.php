<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Aggregate;

use CliffordVickrey\Book2024\Common\Entity\FecBulk\Candidate;
use CliffordVickrey\Book2024\Common\Entity\PropOrder;
use CliffordVickrey\Book2024\Common\Entity\ValueObject\Jurisdiction;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type IndexedCandidateInfo non-empty-array<int, non-empty-array<string, Candidate>>
 */
class CandidateAggregate extends Aggregate
{
    #[PropOrder(1)]
    public string $name = '';
    /** @var list<Candidate> */
    #[PropOrder(2)]
    public array $info = [];
    /** @var array<string, bool> */
    #[PropOrder(3)]
    public array $democraticNominations = [];
    /** @var array<string, bool> */
    #[PropOrder(4)]
    public array $republicanNominations = [];
    /** @var IndexedCandidateInfo|null */
    private ?array $indexedInfo = null;

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

    public function getInfo(?int $year = null, ?string $candidateId = null, bool $fallback = true): ?Candidate
    {
        $info = $this->doGetInfo($year, $candidateId);

        if ($info || !$fallback) {
            return $info;
        }

        if (null !== $year) {
            return $this->getInfo(candidateId: $candidateId);
        }

        if (null !== $candidateId) {
            return $this->getInfo($year);
        }

        return null;
    }

    private function doGetInfo(?int $year, ?string $candidateId): ?Candidate
    {
        $indexed = $this->getIndexedCandidateInfo();

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

    /**
     * @return IndexedCandidateInfo
     */
    private function getIndexedCandidateInfo(): array
    {
        $this->indexedInfo ??= $this->buildIndexedCandidateInfo();

        return $this->indexedInfo;
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

    public function isNominee(int $year, Jurisdiction $jurisdiction, ?bool $democrat = null): bool
    {
        $key = \sprintf('%d%s', $year, $jurisdiction);
        $isNominee = $this->democraticNominations[$key] ?? false;

        if (!$isNominee || false === $democrat) {
            $isNominee = $this->republicanNominations[$key] ?? false;
        }

        return $isNominee;
    }
}
