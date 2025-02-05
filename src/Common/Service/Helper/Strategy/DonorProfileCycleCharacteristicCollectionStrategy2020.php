<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service\Helper\Strategy;

use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle2020;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;

/**
 * @extends AbstractDonorProfileCycleCharacteristicCollectionStrategy<DonorProfileCycle2020>
 */
class DonorProfileCycleCharacteristicCollectionStrategy2020 extends AbstractDonorProfileCycleCharacteristicCollectionStrategy
{
    protected function doCollectAttributesForCycle(DonorProfileCycle $profileCycle): array
    {
        $characteristics = [];

        if ($profileCycle->presDonaldTrump->receipts) {
            $characteristics[] = DonorCharacteristic::cycle_2020_trump;
        }

        if ($profileCycle->presJoeBiden->receipts) {
            $characteristics[] = DonorCharacteristic::cycle_2020_biden;
        }

        if (
            $profileCycle->presBernieSanders->receipts
            || $profileCycle->presElizabethWarren->receipts
        ) {
            $characteristics[] = DonorCharacteristic::cycle_2020_progressive;
        }

        if (!$profileCycle->presJoeBiden->receipts) {
            $characteristics[] = DonorCharacteristic::cycle_2020_non_biden;
        }

        return $characteristics;
    }
}
