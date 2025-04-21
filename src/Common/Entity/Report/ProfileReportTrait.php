<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\Report;

use CliffordVickrey\Book2024\Common\Entity\Profile\Campaign\DonorProfileCampaign;
use CliffordVickrey\Book2024\Common\Entity\Profile\DonorProfile;
use CliffordVickrey\Book2024\Common\Enum\CampaignType;
use CliffordVickrey\Book2024\Common\Utilities\MathUtilities;

trait ProfileReportTrait
{
    public function setProfile(DonorProfile $profile): void
    {
        array_walk($profile->campaigns, $this->setProfileCampaign(...));
    }

    private function setProfileCampaign(DonorProfileCampaign $campaign): void
    {
        if (0 === $campaign->total->receipts) {
            return;
        }

        switch ($campaign->campaignType) {
            case CampaignType::joe_biden:
                $this->biden_amt = MathUtilities::add($this->biden_amt, $campaign->total->amount);
                $this->biden_receipts += $campaign->total->receipts;
                ++$this->biden_donors;
                break;
            case CampaignType::kamala_harris:
                $this->harris_amt = MathUtilities::add($this->harris_amt, $campaign->total->amount);
                $this->harris_receipts += $campaign->total->receipts;
                ++$this->harris_donors;
                break;
            default:
                $this->trump_amt = MathUtilities::add($this->trump_amt, $campaign->total->amount);
                $this->trump_receipts += $campaign->total->receipts;
                ++$this->trump_donors;
        }
    }
}
