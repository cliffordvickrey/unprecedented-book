<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service\Helper\Strategy;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\Profile\Campaign\DonorProfileCampaign;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;

/**
 * @implements DonorProfileCharacteristicCollectionStrategyInterface<DonorProfileCampaign>
 */
final readonly class DonorProfileCampaignCharacteristicCollectionStrategy implements DonorProfileCharacteristicCollectionStrategyInterface
{
    public const int DEFAULT_WEEKLY_THRESHOLD = 16;
    public const int DEFAULT_MONTHLY_THRESHOLD = 4;

    /**
     * @param positive-int $monthlyThreshold
     * @param positive-int $weeklyThreshold
     */
    public function __construct(
        private int $monthlyThreshold = self::DEFAULT_MONTHLY_THRESHOLD,
        private int $weeklyThreshold = self::DEFAULT_WEEKLY_THRESHOLD,
    ) {
    }

    public function collectCharacteristics(Entity $entity): array
    {
        $characteristics = [self::getAmtCharacteristic($entity)];

        if ($entity->priorDonor) {
            $characteristics[] = DonorCharacteristic::prior;
        }

        if ($entity->dayOneLaunch) {
            $characteristics[] = DonorCharacteristic::day_one_launch;
        }

        if ($entity->weekOneLaunch) {
            $characteristics[] = DonorCharacteristic::week_one_launch;
        }

        if ($entity->maxConsecutiveMonthlyDonationCount >= $this->monthlyThreshold) {
            $characteristics[] = DonorCharacteristic::monthly;
        }

        if ($entity->maxConsecutiveWeeklyDonationCount > $this->weeklyThreshold) {
            $characteristics[] = DonorCharacteristic::weekly;
        }

        return $characteristics;
    }

    private static function getAmtCharacteristic(DonorProfileCampaign $campaign): DonorCharacteristic
    {
        $rounded = round($campaign->total->amount, 2);

        return match (true) {
            ($rounded <= 1.0) => DonorCharacteristic::amt_up_to_1,
            ($rounded < 200.0) => DonorCharacteristic::amt_up_to_200,
            ($rounded < 1000.0) => DonorCharacteristic::amt_up_to_1000,
            ($rounded < 2800.0) => DonorCharacteristic::amt_up_to_2800,
            default => DonorCharacteristic::amt_more_than_2800,
        };
    }
}
