<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service;

use CliffordVickrey\Book2024\Common\Entity\Combined\DonorPanel;
use CliffordVickrey\Book2024\Common\Entity\Profile\DonorProfile;
use CliffordVickrey\Book2024\Common\Enum\CampaignType;
use CliffordVickrey\Book2024\Common\Service\DTO\DonorCharacteristicCollection;

interface DonorProfileServiceInterface
{
    public function buildDonorProfile(DonorPanel $panel): DonorProfile;

    public function collectDonorCharacteristics(DonorPanel|DonorProfile $panelOrProfile): DonorCharacteristicCollection;

    public function serializeDonorProfile(DonorProfile $profile): string;

    /**
     * @return list<string>
     */
    public function getCampaignCommitteeSlugs(CampaignType $campaignType): array;
}
