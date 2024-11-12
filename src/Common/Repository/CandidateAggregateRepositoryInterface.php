<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Repository;

use CliffordVickrey\Book2024\Common\Entity\Aggregate\CandidateAggregate;
use CliffordVickrey\Book2024\Common\Entity\ValueObject\Jurisdiction;

/**
 * @extends AggregateRepositoryInterface<CandidateAggregate>
 */
interface CandidateAggregateRepositoryInterface extends AggregateRepositoryInterface
{
    public function getNominee(int $year, Jurisdiction $jurisdiction, bool $isDemocratic = true): ?CandidateAggregate;

    /**
     * @return list<CandidateAggregate>
     */
    public function getByYearAndJurisdiction(int $year, Jurisdiction $jurisdiction): array;

    public function getByCandidateId(string $candidateId): CandidateAggregate;
}
