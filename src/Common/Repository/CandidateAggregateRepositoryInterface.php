<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Repository;

use CliffordVickrey\Book2024\Common\Entity\Aggregate\CandidateAggregate;

/**
 * @extends AggregateRepositoryInterface<CandidateAggregate>
 */
interface CandidateAggregateRepositoryInterface extends AggregateRepositoryInterface
{
    public function getByCandidateId(string $candidateId): CandidateAggregate;
}
