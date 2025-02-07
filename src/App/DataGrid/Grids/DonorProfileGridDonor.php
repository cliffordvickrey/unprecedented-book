<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\DataGrid\Grids;

use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristicGenre;

final class DonorProfileGridDonor extends DonorProfileGrid
{
    public function getGenre(): DonorCharacteristicGenre
    {
        return DonorCharacteristicGenre::donor;
    }
}
