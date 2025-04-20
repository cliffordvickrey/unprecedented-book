<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Repository;

use CliffordVickrey\Book2024\Common\Entity\Geo\ZipCode;

/**
 * @extends AggregateRepositoryInterface<ZipCode>
 */
interface ZipCodeRepositoryInterface extends AggregateRepositoryInterface
{
    public function getByZip(string|int $zip): ZipCode;
}
