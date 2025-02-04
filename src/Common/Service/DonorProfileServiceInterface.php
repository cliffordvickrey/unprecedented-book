<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service;

use CliffordVickrey\Book2024\Common\Entity\Combined\DonorPanel;
use CliffordVickrey\Book2024\Common\Entity\Profile\DonorProfile;
use CliffordVickrey\Book2024\Common\Service\DTO\DonorCharacteristicCollection;

interface DonorProfileServiceInterface
{
    public function buildDonorProfile(DonorPanel $panel): DonorProfile;

    public function colorDonorCharacteristics(DonorPanel|DonorProfile $panelOrProfile): DonorCharacteristicCollection;

    public function serializeDonorProfile(DonorProfile $profile): string;
}
