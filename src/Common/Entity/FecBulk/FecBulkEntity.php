<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Entity\FecBulk;

use CliffordVickrey\Book2024\Common\Entity\Entity;
use CliffordVickrey\Book2024\Common\Entity\PropMeta;

abstract class FecBulkEntity extends Entity
{
    #[PropMeta(0)]
    public int $file_id = 0; // file ID representing election year
}
