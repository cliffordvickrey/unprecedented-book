<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service\Collector\Strategy;

use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle;
use CliffordVickrey\Book2024\Common\Entity\Profile\Cycle\DonorProfileCycle2016;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;
use CliffordVickrey\Book2024\Common\Enum\PartyType;

/**
 * @extends AbstractDonorProfileCycleCharacteristicCollectionStrategy<DonorProfileCycle2016>
 */
class DonorProfileCycleCharacteristicCollectionStrategy2016 extends AbstractDonorProfileCycleCharacteristicCollectionStrategy
{
    protected function doCollectAttributesForCycle(DonorProfileCycle $profileCycle): array
    {
        $characteristics = [];

        if ($profileCycle->presHillaryClinton->receipts) {
            $characteristics[] = DonorCharacteristic::cycle_2016_clinton;
        }

        if ($profileCycle->presDonaldTrump->receipts) {
            $characteristics[] = DonorCharacteristic::cycle_2016_trump;
        }

        if ($this->hasPresReceiptsByPartyForCycle($profileCycle, PartyType::democratic)) {
            $characteristics[] = DonorCharacteristic::cycle_2016_dem_pres;
        }

        if ($this->hasPresReceiptsByPartyForCycle($profileCycle, PartyType::republican)) {
            $characteristics[] = DonorCharacteristic::cycle_2016_gop_pres;
        }

        return $characteristics;
    }
}
