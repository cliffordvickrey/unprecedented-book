<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\DataGrid\Grids;

use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristicGenre;

final class DonorProfileGridCycle2024 extends DonorProfileGrid
{
    public function getGenre(): DonorCharacteristicGenre
    {
        return DonorCharacteristicGenre::cycle_2024;
    }
}
