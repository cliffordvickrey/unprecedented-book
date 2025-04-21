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

    public function hasZip(int|string $zip): bool
    {
        try {
            $this->getByZip($zip);
        } catch (\InvalidArgumentException $ex) {
            if (str_starts_with($ex->getMessage(), 'File does not exist')) {
                return false;
            }

            throw $ex;
        }

        return true;
    }
}
