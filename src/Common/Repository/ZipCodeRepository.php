<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Repository;

use CliffordVickrey\Book2024\Common\Entity\Geo\ZipCode;

/**
 * @extends AggregateRepository<ZipCode>
 */
class ZipCodeRepository extends AggregateRepository implements ZipCodeRepositoryInterface
{
    protected function getDirectory(): string
    {
        return 'zip';
    }

    protected function getClassname(): string
    {
        return ZipCode::class;
    }

    public function getByZip(int|string $zip): ZipCode
    {
        return $this->getAggregate(ZipCode::slugifyZip($zip));
    }
}
