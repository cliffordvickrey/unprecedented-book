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
    /** @var array<int, CandidateCommitteeLinkage> */
    #[PropOrder(5)]
    public array $cclByYear = [];
    /** @var array<int, LeadershipPacLinkage> */
    #[PropOrder(6)]
    public array $leadershipPacLinkageByYear = [];
}
