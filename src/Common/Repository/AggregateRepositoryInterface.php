<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Repository;

use CliffordVickrey\Book2024\Common\Entity\Aggregate\Aggregate;

/**
 * @template TAggregate of Aggregate
 */
interface AggregateRepositoryInterface
{
    /**
     * @phpstan-return TAggregate
     */
    public function getAggregate(string $slug): Aggregate;

    /**
     * @return list<string>
     */
    public function getAllSlugs(): array;

    /**
     * @phpstan-param TAggregate $aggregate
     */
    public function saveAggregate(Aggregate $aggregate): void;
}
