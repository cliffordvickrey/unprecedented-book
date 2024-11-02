<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Repository;

use CliffordVickrey\Book2024\Common\Entity\Aggregate\Aggregate;
use CliffordVickrey\Book2024\Common\Utilities\FileIterator;
use CliffordVickrey\Book2024\Common\Utilities\FileUtilities;
use CliffordVickrey\Book2024\Common\Utilities\JsonUtilities;
use Webmozart\Assert\Assert;

/**
 * @implements AggregateRepositoryInterface<TAggregate>
 *
 * @template TAggregate of Aggregate
 */
abstract class AggregateRepository implements AggregateRepositoryInterface
{
    /** @var array<string, TAggregate> */
    private array $memo = [];

    public function getAggregate(string $slug): Aggregate
    {
        if (isset($this->memo[$slug])) {
            return $this->memo[$slug];
        }

        $filename = $this->getFilename($slug);

        $json = FileUtilities::getContents($filename);

        $classStr = $this->getClassname();

        $aggregate = $classStr::fromJson($json);
        $this->memo[$slug] = $aggregate;

        return $aggregate;
    }

    public function deleteAll(): void
    {
        // careful!
        FileUtilities::unlink($this->getDirname(), recursive: true);
    }

    public function saveAggregate(Aggregate $aggregate): void
    {
        $filename = $this->getFilename($aggregate->slug);

        FileUtilities::saveContents($filename, JsonUtilities::jsonEncode($aggregate, true));

        $this->memo[$aggregate->slug] = $aggregate;
    }

    protected function getFilename(string $slug): string
    {
        $slug = preg_replace('/[^a-zA-Z0-9_]/', '', $slug);

        Assert::string($slug);

        $subDir = substr($slug, 0, 1);

        if ('' === $subDir) {
            $subDir = '_';
        }

        return __DIR__."/../../../data/aggregate/{$this->getDirectory()}/$subDir/$slug.json";
    }

    /**
     * @return list<string>
     */
    protected function getAllSlugs(): array
    {
        $filenames = FileIterator::getFilenames($this->getDirname(), 'json');

        return array_map(static fn (string $filename) => basename($filename, '.json'), $filenames);
    }

    protected function getDirname(): string
    {
        $filename = $this->getFilename('_');

        return \dirname($filename, 2);
    }

    abstract protected function getDirectory(): string;

    /**
     * @return class-string<TAggregate>
     */
    abstract protected function getClassname(): string;
}
