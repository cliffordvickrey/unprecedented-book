<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service;

use CliffordVickrey\Book2024\Common\Entity\Combined\DonorPanel;
use CliffordVickrey\Book2024\Common\Entity\Profile\DonorProfile;

interface DonorProfileServiceInterface
{
    public function buildDonorProfile(DonorPanel $panel): DonorProfile;
}
