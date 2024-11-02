<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Aggregate;

use CliffordVickrey\Book2024\Common\Entity\FecBulk\CandidateCommitteeLinkage;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\Committee;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\LeadershipPacLinkage;
use CliffordVickrey\Book2024\Common\Entity\PropOrder;
use CliffordVickrey\Book2024\Common\Entity\ValueObject\CommitteeTotals;

class CommitteeAggregate extends Aggregate
{
    #[PropOrder(1)]
    public string $name = '';
    #[PropOrder(2)]
    public string $id = '';
    /** @var array<int, CommitteeTotals> */
    #[PropOrder(3)]
    public array $committeeTotalsByYear = [];
    /** @var array<int, Committee> */
    #[PropOrder(4)]
    public array $infoByYear = [];
    /** @var list<CandidateCommitteeLinkage> */
    #[PropOrder(5)]
    public array $ccl = [];
    /** @var list<LeadershipPacLinkage> */
    #[PropOrder(6)]
    public array $leadershipPacLinkage = [];
    public ?string $primaryCandidateSlug = null;
    /** @var list<string> */
    public array $candidateSlugs = [];

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
}
