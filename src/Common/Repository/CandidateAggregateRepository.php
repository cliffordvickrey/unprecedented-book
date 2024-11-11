<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Repository;

use CliffordVickrey\Book2024\Common\Entity\Aggregate\CandidateAggregate;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\Candidate;
use CliffordVickrey\Book2024\Common\Entity\ValueObject\Jurisdiction;
use CliffordVickrey\Book2024\Common\Exception\BookOutOfBoundsException;
use CliffordVickrey\Book2024\Common\Utilities\FileUtilities;
use CliffordVickrey\Book2024\Common\Utilities\JsonUtilities;

/**
 * @extends AggregateRepository<CandidateAggregate>
 */
final class CandidateAggregateRepository extends AggregateRepository implements CandidateAggregateRepositoryInterface
{
    /** @var array<string, string>|null */
    private ?array $slugsByCandidateId = null;
    /** @var array<int, array<string, list<string>>>|null */
    private ?array $slugsByYearAndJurisdiction = null;

    public function hasCandidateId(string $candidateId): bool
    {
        try {
            $this->getByCandidateId($candidateId);
        } catch (BookOutOfBoundsException) {
            return false;
        }

        return true;
    }

    public function getByCandidateId(string $candidateId): CandidateAggregate
    {
        $slugsByCandidateId = $this->getSlugsByCandidateId();

        $slug = $slugsByCandidateId[$candidateId] ?? null;

        if (null === $slug) {
            $msg = \sprintf('Unknown candidate ID: %s', $candidateId);
            throw new BookOutOfBoundsException($msg);
        }

        return $this->getAggregate($slug);
    }

    /**
     * @return array<string, string>
     */
    private function getSlugsByCandidateId(): array
    {
        $this->slugsByCandidateId ??= $this->resolveSlugsByCandidateId();

        return $this->slugsByCandidateId;
    }

    /**
     * @return array<int, array<string, list<string>>>
     */
    private function getSlugsByYearAndJurisdiction(): array
    {
        $this->slugsByYearAndJurisdiction ??= $this->resolveSlugsByYearAndJurisdiction();

        return $this->slugsByYearAndJurisdiction;
    }

    /**
     * @return array<string, string>
     */
    private function resolveSlugsByCandidateId(): array
    {
        $filename = $this->getDirname().\DIRECTORY_SEPARATOR.'slugs-by-candidate-id.json';

        if (is_file($filename)) {
            $json = FileUtilities::getContents($filename);

            return JsonUtilities::jsonDecode($json);
        }

        $map = $this->mapSlugsByCandidateId();

        FileUtilities::saveContents($filename, JsonUtilities::jsonEncode($map, true));

        return $map;
    }

    /**
     * @return array<string, string>
     */
    private function mapSlugsByCandidateId(): array
    {
        $slugs = $this->getAllSlugs();

        // (extremely nerds voice) my Lisp-like higher-order functions
        return array_reduce($slugs, function (array $carry, string $slug): array {
            $aggregate = $this->getAggregate($slug);

            $candidateIds = array_values(array_unique(array_map(
                static fn (Candidate $info) => $info->CAND_ID,
                $aggregate->info
            )));

            return array_merge($carry, array_combine($candidateIds, array_fill(0, \count($candidateIds), $slug)));
        }, []);
    }

    protected function getDirectory(): string
    {
        return 'cn';
    }

    protected function getClassname(): string
    {
        return CandidateAggregate::class;
    }

    /**
     * @return array<int, array<string, list<string>>>
     */
    private function resolveSlugsByYearAndJurisdiction(): array
    {
        $filename = $this->getDirname().\DIRECTORY_SEPARATOR.'slugs-by-year-and-jurisdiction.json';

        if (is_file($filename)) {
            $json = FileUtilities::getContents($filename);

            return JsonUtilities::jsonDecode($json);
        }

        $map = $this->mapSlugsByYearAndJurisdiction();

        FileUtilities::saveContents($filename, JsonUtilities::jsonEncode($map, true));

        return $map;
    }

    /**
     * @return array<int, array<string, list<string>>>
     */
    private function mapSlugsByYearAndJurisdiction(): array
    {
        $slugs = $this->getAllSlugs();

        // (extremely nerds voice) my Lisp-like higher-order functions
        $slugsByYearAndJurisdiction = array_reduce($slugs, function (array $carry, string $slug): array {
            $aggregrate = $this->getAggregate($slug);

            foreach ($aggregrate->info as $candidate) {
                $jurisdiction = (string) $candidate->getJurisdiction();

                if ('' === $jurisdiction) {
                    continue;
                }

                $year = $candidate->CAND_ELECTION_YR;

                if (null === $year) {
                    continue;
                }

                if (!isset($carry[$year])) {
                    $carry[$year] = [];
                }

                if (!isset($carry[$year][$jurisdiction])) {
                    $carry[$year][$jurisdiction] = [];
                }

                if (\in_array($slug, $carry[$year][$jurisdiction])) {
                    continue;
                }

                $carry[$year][$jurisdiction][] = $slug;
            }

            return $carry;
        }, []);

        ksort($slugsByYearAndJurisdiction);

        array_walk(
            $slugsByYearAndJurisdiction,
            static fn (array &$slugsByJurisdiction) => ksort($slugsByJurisdiction)
        );

        return $slugsByYearAndJurisdiction;
    }

    public function getByYearAndJurisdiction(int $year, Jurisdiction $jurisdiction): array
    {
        $slugs = $this->getSlugsByYearAndJurisdiction();

        $matchedSlugs = $slugs[$year][(string) $jurisdiction] ?? [];

        return array_map(fn (string $slug) => $this->getAggregate($slug), $matchedSlugs);
    }
}
