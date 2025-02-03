<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Profile\Campaign;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\Profile\DonorProfileAmount;
use CliffordVickrey\Book2024\Common\Enum\CampaignType;

class DonorProfileCampaign extends Entity
{
    public CampaignType $campaignType;
    public int $maxConsecutiveMonthlyDonationCount = 0;
    public int $maxConsecutiveWeeklyDonationCount = 0;
    public bool $priorDonor = false;
    public DonorProfileAmount $total;
    public bool $weekOneLaunch = false;
}
