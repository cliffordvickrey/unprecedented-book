<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service\Collector\Strategy;

use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle2024;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;

/**
 * @extends AbstractDonorProfileCycleCharacteristicCollectionStrategy<DonorProfileCycle2024>
 */
class DonorProfileCycleCharacteristicCollectionStrategy2024 extends AbstractDonorProfileCycleCharacteristicCollectionStrategy
{
    protected function doCollectAttributesForCycle(DonorProfileCycle $profileCycle): array
    {
        $characteristics = [];

        if ($profileCycle->presJoeBiden->receipts) {
            $characteristics[] = DonorCharacteristic::cycle_2024_biden;
        }

        if ($profileCycle->presKamalaHarris->receipts) {
            $characteristics[] = DonorCharacteristic::cycle_2024_harris;
        }

        if ($profileCycle->presDonaldTrump->receipts) {
            $characteristics[] = DonorCharacteristic::cycle_2024_trump;
        }

        if ($profileCycle->presRonDeSantis->receipts) {
            $characteristics[] = DonorCharacteristic::cycle_2024_desantis;
        }

        if ($profileCycle->presNikkiHaley->receipts) {
            $characteristics[] = DonorCharacteristic::cycle_2024_haley;
        }

        if ($profileCycle->presRfkJr->receipts) {
            $characteristics[] = DonorCharacteristic::cycle_2024_rfk_jr;
        }

        if ($this->hasPresReceiptsForCycle($profileCycle) && !$profileCycle->presDonaldTrump->receipts) {
            $characteristics[] = DonorCharacteristic::cycle_2024_non_trump;
        }

        return $characteristics;
    }
}
