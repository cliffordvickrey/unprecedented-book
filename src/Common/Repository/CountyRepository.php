<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Repository;

use CliffordVickrey\Book2024\Common\Entity\Geo\County;

/**
 * @extends AggregateRepository<County>
 */
class CountyRepository extends AggregateRepository
{
    protected function getDirectory(): string
    {
        return 'counties';
    }

    protected function getClassname(): string
    {
        return County::class;
    }
}
