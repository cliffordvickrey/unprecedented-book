<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Repository;

use CliffordVickrey\Book2024\Common\Entity\Aggregate\CandidateAggregate;
use CliffordVickrey\Book2024\Common\Entity\FecBulk\Candidate;
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
     * @return array<string, string>
     */
    private function resolveSlugsByCandidateId(): array
    {
        $filename = $this->getDirname().\DIRECTORY_SEPARATOR.'slugs-by-candidate-id.csv';

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
}
