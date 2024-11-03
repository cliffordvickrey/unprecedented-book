<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Repository;

use CliffordVickrey\Book2024\Common\Entity\Aggregate\CommitteeAggregate;
use CliffordVickrey\Book2024\Common\Exception\BookOutOfBoundsException;
use CliffordVickrey\Book2024\Common\Utilities\FileUtilities;
use CliffordVickrey\Book2024\Common\Utilities\JsonUtilities;

/**
 * @extends AggregateRepository<CommitteeAggregate>
 */
final class CommitteeAggregateRepository extends AggregateRepository implements CommitteeAggregateRepositoryInterface
{
    /** @var array<string, string>|null */
    private ?array $slugsByCommitteeId = null;

    public function hasCommitteeId(string $committeeId): bool
    {
        try {
            $this->getByCommitteeId($committeeId);
        } catch (BookOutOfBoundsException) {
            return false;
        }

        return true;
    }

    public function getByCommitteeId(string $committeeId): CommitteeAggregate
    {
        $slugsByCommitteeId = $this->getSlugsByCommitteeId();

        $slug = $slugsByCommitteeId[$committeeId] ?? null;

        if (null === $slug) {
            $msg = \sprintf('Unknown committee ID: %s', $committeeId);
            throw new BookOutOfBoundsException($msg);
        }

        return $this->getAggregate($slug);
    }

    /**
     * @return array<string, string>
     */
    private function getSlugsByCommitteeId(): array
    {
        $this->slugsByCommitteeId ??= $this->resolveSlugsByCommitteeId();

        return $this->slugsByCommitteeId;
    }

    /**
     * @return array<string, string>
     */
    private function resolveSlugsByCommitteeId(): array
    {
        $filename = $this->getDirname().\DIRECTORY_SEPARATOR.'slugs-by-committee-id.csv';

        if (is_file($filename)) {
            $json = FileUtilities::getContents($filename);

            return JsonUtilities::jsonDecode($json);
        }

        $map = $this->mapSlugsByCommitteeId();

        FileUtilities::saveContents($filename, JsonUtilities::jsonEncode($map, true));

        return $map;
    }

    /**
     * @return array<string, string>
     */
    private function mapSlugsByCommitteeId(): array
    {
        return array_reduce(
            $this->getAllSlugs(),
            fn (array $carry, string $slug) => array_merge($carry, [$this->getAggregate($slug)->id => $slug]),
            []
        );
    }

    protected function getDirectory(): string
    {
        return 'cm';
    }

    protected function getClassname(): string
    {
        return CommitteeAggregate::class;
    }

    protected function getSubDir(string $slug): string
    {
        $trailing = parent::getSubDir($slug);

        $leading = 'pac';

        if (str_contains($slug, '-')) {
            $leading = 'cand';
        }

        return \sprintf('%s-%s', $leading, $trailing);
    }
}
