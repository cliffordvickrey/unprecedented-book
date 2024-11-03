<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Repository;

use CliffordVickrey\Book2024\Common\Entity\Aggregate\CommitteeAggregate;

/**
 * @extends AggregateRepositoryInterface<CommitteeAggregate>
 */
interface CommitteeAggregateRepositoryInterface extends AggregateRepositoryInterface
{
    public function getByCommitteeId(string $committeeId): CommitteeAggregate;
}
