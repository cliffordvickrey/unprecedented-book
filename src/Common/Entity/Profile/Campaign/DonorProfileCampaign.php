<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Profile\Campaign;

use CliffordVickrey\Book2024\Common\Entity\Profile\DonorProfileAmount;

class DonorProfileCampaign
{
    public int $maxMonthlyCount = 0;
    public bool $priorDonor = false;
    public DonorProfileAmount $total;
    public bool $weekOneLaunch = false;
}
