<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service\Collector\Strategy;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\Profile\Campaign\DonorProfileCampaign;

/**
 * @implements DonorProfileCharacteristicCollectionStrategyInterface<DonorProfileCampaign>
 */
class DonorProfileCampaignCharacteristicCollectionStrategy implements DonorProfileCharacteristicCollectionStrategyInterface
{
    public function collectCharacteristics(Entity $entity): array
    {
        return [];
    }
}
