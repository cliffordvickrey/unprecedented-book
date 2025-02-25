<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Profile\Campaign;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\Profile\DonorProfileAmount;
use CliffordVickrey\Book2024\Common\Enum\CampaignType;

class DonorProfileCampaign extends Entity implements \Countable
{
    public bool $afterBidenDropout = false;
    public bool $beforeBidenDropout = false;
    public CampaignType $campaignType;
    public bool $dayOneLaunch = false;
    /** @var array<string, DonorProfileAmount> */
    public array $donationsByDate = [];
    public int $maxConsecutiveMonthlyDonationCount = 0;
    public int $maxConsecutiveWeeklyDonationCount = 0;
    public bool $priorDonor = false;
    public bool $priorOpponentDonor = false;
    public DonorProfileAmount $total;
    public bool $weekOneLaunch = false;

    public function count(): int
    {
        return $this->total->receipts;
    }
}
