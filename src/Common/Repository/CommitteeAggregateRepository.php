<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Repository;

use CliffordVickrey\Book2024\Common\Entity\Aggregate\CommitteeAggregate;
use CliffordVickrey\Book2024\Common\Enum\Fec\CandidateOffice;
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
    /** @var array<string, array<int, list<string>>>|null */
    private ?array $slugsByCommitteeNameAndYear = null;

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

    public function getByCommitteeName(
        string $committeeName,
        ?int $year = null,
        bool $fallback = false,
    ): ?CommitteeAggregate {
        $committeeName = trim(strtoupper($committeeName));

        if ('' === $committeeName) {
            return null;
        }

        $slugsByCommitteeId = $this->getSlugsByCommitteeNameAndYear();

        $slugsByYear = $slugsByCommitteeId[$committeeName] ?? null;

        if (empty($slugsByYear)) {
            return null;
        }

        $key = $year ?? array_key_last($slugsByYear);

        $slugs = $slugsByYear[$key] ?? null;

        if (empty($slugs) && $year && $fallback) {
            return $this->getByCommitteeName($committeeName);
        } elseif (empty($slugs)) {
            return null;
        }

        return $this->getAggregate($slugs[array_key_first($slugs)]);
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
     * @return array<string, array<int, list<string>>>
     */
    private function getSlugsByCommitteeNameAndYear(): array
    {
        $this->slugsByCommitteeNameAndYear ??= $this->resolveSlugsByCommitteeNameAndYear();

        return $this->slugsByCommitteeNameAndYear;
    }

    /**
     * @return array<string, string>
     */
    private function resolveSlugsByCommitteeId(): array
    {
        $filename = $this->getDirname().\DIRECTORY_SEPARATOR.'slugs-by-committee-id.json';

        if (is_file($filename)) {
            $json = FileUtilities::getContents($filename);

            return JsonUtilities::jsonDecode($json);
        }

        $map = $this->mapSlugsByCommitteeId();

        FileUtilities::saveContents($filename, JsonUtilities::jsonEncode($map, true));

        return $map;
    }

    /**
     * @return array<string, array<int, list<string>>>
     */
    private function resolveSlugsByCommitteeNameAndYear(): array
    {
        $filename = $this->getDirname().\DIRECTORY_SEPARATOR.'slugs-by-committee-name-and-year.json';

        if (is_file($filename)) {
            $json = FileUtilities::getContents($filename);

            return JsonUtilities::jsonDecode($json);
        }

        $map = $this->mapSlugsByCommitteeNameAndYear();

        FileUtilities::saveContents($filename, JsonUtilities::jsonEncode($map, true));

        return $map;
    }

    /**
     * @return array<string, array<int, list<string>>>
     */
    private function mapSlugsByCommitteeNameAndYear(): array
    {
        // @phpstan-ignore-next-line
        return array_reduce(
            $this->getAllSlugs(),
            function (array $carry, string $slug): array {
                $aggregate = $this->getAggregate($slug);

                foreach ($aggregate->infoByYear as $year => $committee) {
                    $name = trim((string) $committee->CMTE_NAME);

                    if ('' === $name) {
                        continue;
                    }

                    $name = strtoupper($name);

                    /** @var array<string, array<int, list<string>>> $carry */
                    if (!isset($carry[$name])) {
                        $carry[$name] = [];
                    }

                    if (!isset($carry[$name][$year])) {
                        $carry[$name][$year] = [];
                    }

                    $carry[$name][$year][] = $slug;
                }

                return $carry;
            },
            []
        );
    }

    /**
     * @return array<string, string>
     */
    private function mapSlugsByCommitteeId(): array
    {
        // @phpstan-ignore-next-line
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
        /** @phpstan-var array<string, string> $officeSlugs */
        static $officeSlugs = CandidateOffice::getSlugs();

        $trailing = parent::getSubDir($slug);

        $leading = 'pac';

        $parts = explode('-', $slug);

        $officeParts = explode('_', $parts[1] ?? '');

        if (isset($officeSlugs[$officeParts[0]])) {
            $leading = 'cand';
        }

        return \sprintf('%s-%s', $leading, $trailing);
    }
}
