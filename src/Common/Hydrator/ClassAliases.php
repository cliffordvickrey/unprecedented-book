<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Hydrator;

use CliffordVickrey\Book2024\Common\Entity\Combined\DonorPanel;
use CliffordVickrey\Book2024\Common\Entity\Combined\ReceiptInPanel;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\Candidate;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\CandidateCommitteeLinkage;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\Committee;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\LeadershipPacLinkage;
use CliffordVickrey\Book2024\Common\Entity\Profile\Campaign\DonorProfileCampaign;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle;
use CliffordVickrey\Book2024\Common\Entity\ValueObject\CommitteeTotals;
use CliffordVickrey\Book2024\Common\Entity\ValueObject\ImputedCommitteeTotals;

final class ClassAliases
{
    /** @var array<string, class-string> */
    public static array $aliases = [
        'Candidate' => Candidate::class,
        'CandidateCommitteeLinkage' => CandidateCommitteeLinkage::class,
        'Committee' => Committee::class,
        'CommitteeTotals' => CommitteeTotals::class,
        'DonorPanel' => DonorPanel::class,
        'DonorProfileCampaign' => DonorProfileCampaign::class,
        'DonorProfileCycle' => DonorProfileCycle::class,
        'ImputedCommitteeTotals' => ImputedCommitteeTotals::class,
        'LeadershipPacLinkage' => LeadershipPacLinkage::class,
        'ReceiptInPanel' => ReceiptInPanel::class,
    ];
}
