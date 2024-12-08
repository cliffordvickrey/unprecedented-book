<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Service;

use CliffordVickrey\Book2024\Common\Csv\CsvReader;
use CliffordVickrey\Book2024\Common\Entity\Combined\Receipt;
use CliffordVickrey\Book2024\Common\Repository\CommitteeAggregateRepository;
use CliffordVickrey\Book2024\Common\Repository\CommitteeAggregateRepositoryInterface;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;
use CliffordVickrey\Book2024\Common\Utilities\FileUtilities;

class ReceiptReadingService extends AbstractReceiptService implements ReceiptReadingServiceInterface
{
    public function __construct(private ?CommitteeAggregateRepositoryInterface $repository = null)
    {
    }

    public function readByCommitteeId(string $committeeId, bool $withDonorIds = true): \Generator
    {
        $slug = $this->getRepository()->getByCommitteeId($committeeId)->slug;

        return $this->readByCommitteeSlug($slug, $withDonorIds);
    }

    private function getRepository(): CommitteeAggregateRepositoryInterface
    {
        $this->repository ??= new CommitteeAggregateRepository();

        return $this->repository;
    }

    public function readByCommitteeSlug(string $committeeSlug, bool $withDonorIds = true): \Generator
    {
        $filename = FileUtilities::getAbsoluteCanonicalPath($this->getFilename($committeeSlug, $withDonorIds));

        printf('Reading %s%s', $filename, \PHP_EOL);

        $reader = new CsvReader($filename);
        $headers = array_map(\strval(...), array_map(CastingUtilities::toString(...), $reader->current()));
        $reader->next();

        while ($reader->valid()) {
            yield Receipt::__set_state(array_combine($headers, $reader->current()));
            $reader->next();
        }

        $reader->close();
    }
}
