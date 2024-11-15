<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Aggregate;

use CliffordVickrey\Book2024\Common\Entity\FecBulk\CandidateCommitteeLinkage;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\Committee;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\LeadershipPacLinkage;
use CliffordVickrey\Book2024\Common\Entity\PropMeta;
use CliffordVickrey\Book2024\Common\Entity\ValueObject\CommitteeTotals;
use CliffordVickrey\Book2024\Common\Entity\ValueObject\ImputedCommitteeTotals;
use CliffordVickrey\Book2024\Common\Enum\Fec\CommitteeDesignation;

class CommitteeAggregate extends Aggregate
{
    #[PropMeta(1)]
    public string $name = '';
    #[PropMeta(2)]
    public string $id = '';
    /** @var array<int, CommitteeTotals> */
    #[PropMeta(3)]
    public array $committeeTotalsByYear = [];
    /** @var array<int, ImputedCommitteeTotals> */
    #[PropMeta(4)]
    public array $imputedCommitteeTotalsByYear = [];
    /** @var array<int, Committee> */
    #[PropMeta(5)]
    public array $infoByYear = [];
    /** @var list<CandidateCommitteeLinkage> */
    #[PropMeta(6)]
    public array $ccl = [];
    /** @var list<LeadershipPacLinkage> */
    #[PropMeta(7)]
    public array $leadershipPacLinkage = [];
    /** @var array<int, string>|null */
    private ?array $candidateIdByYear = null;

    public function getMostActiveYear(): ?int
    {
        $totalsByYear = $this->committeeTotalsByYear;

        if (0 === \count($totalsByYear)) {
            return null;
        }

        if ($this->getAllTimeReceipts() <= 0.0) {
            return null;
        }

        uasort(
            $totalsByYear,
            static fn (CommitteeTotals $a, CommitteeTotals $b) => $a->receipts <=> $b->receipts
        );

        return array_key_last($totalsByYear);
    }

    public function getAllTimeReceipts(): float
    {
        return array_sum(array_map(
            static fn (CommitteeTotals $totals) => $totals->receipts,
            $this->committeeTotalsByYear
        ));
    }

    /**
     * @return list<string>
     */
    public function getCandidateIds(): array
    {
        return array_values(array_unique(array_map(
            static fn (CandidateCommitteeLinkage|LeadershipPacLinkage $pac) => (string) $pac->CAND_ID,
            [...$this->ccl, ...$this->leadershipPacLinkage]
        )));
    }

    public function getCandidateSlug(): ?string
    {
        $parts = explode('-', $this->slug);

        if (\count($parts) > 1) {
            return array_shift($parts);
        }

        return null;
    }

    public function getCandidateIdByYear(int $year): ?string
    {
        if (null === $this->candidateIdByYear) {
            $this->candidateIdByYear = $this->resolveCandidateIdByYear();
        }

        return $this->candidateIdByYear[$year] ?? null;
    }

    /**
     * @return array<int, string>
     */
    private function resolveCandidateIdByYear(): array
    {
        $candIds = array_reduce($this->ccl, function (array $carry, CandidateCommitteeLinkage $ccl): array {
            if (isset($carry[$ccl->file_id])) {
                return $carry;
            }

            $cclsInFile = array_filter(
                $this->ccl,
                static fn (CandidateCommitteeLinkage $_ccl) => $_ccl->file_id === $ccl->file_id
            );

            if (1 === \count($cclsInFile) || CommitteeDesignation::P === $ccl->CMTE_DSGN) {
                $carry[$ccl->file_id] = $ccl->CAND_ID;
            }

            return $carry;
        }, []);

        $lpls = $this->leadershipPacLinkage;

        /** @var array<int, string> $candIds */
        $candIds = array_reduce($lpls, static function (array $carry, LeadershipPacLinkage $lpl): array {
            $carry[$lpl->file_id] = $lpl->CAND_ID;

            return $carry;
        }, $candIds);

        ksort($candIds, \SORT_NUMERIC);

        return $candIds;
    }
}
