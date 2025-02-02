<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Repository;

use CliffordVickrey\Book2024\Common\Entity\Combined\DonorPanel;
use CliffordVickrey\Book2024\Common\Entity\Combined\DonorPanelCollection;
use CliffordVickrey\Book2024\Common\Utilities\FileIterator;
use CliffordVickrey\Book2024\Common\Utilities\FileUtilities;
use CliffordVickrey\Book2024\Common\Utilities\JsonUtilities;
use Webmozart\Assert\Assert;

class DonorPanelRepository implements DonorPanelRepositoryInterface
{
    private readonly string $path;
    /** @var list<string>|null */
    private ?array $filenames = null;

    public function __construct(?string $path = null, private readonly bool $prettyPrint = false)
    {
        $this->path = $path ?? __DIR__.'/../../../data/panel';
    }

    /**
     * @return list<string>
     */
    private function getFilenames(?string $state, ?int $chunkId): array
    {
        $filter = function (string $filename) use ($state, $chunkId): bool {
            if (null !== $state) {
                $dirname = \dirname($filename);

                if (strtolower($dirname) !== strtolower($state)) {
                    return false;
                }
            }

            if (null !== $chunkId) {
                $basename = basename($filename, '.json');

                $trailing = preg_replace('/^chunk/', '', $basename);

                $matchedChunkId = null;

                if (is_numeric($trailing)) {
                    $matchedChunkId = (int) $trailing;
                }

                if ($matchedChunkId !== $chunkId) {
                    return false;
                }
            }

            return true;
        };

        return array_values(array_filter($this->getAllFilenames(), $filter));
    }

    /**
     * @return list<string>
     */
    private function getAllFilenames(): array
    {
        $this->filenames ??= FileIterator::getFilenames($this->path, 'json');

        return $this->filenames;
    }

    public function get(?string $state = null, ?int $chunkId = null): \Generator
    {
        $filenames = $this->getFilenames($state, $chunkId);

        foreach ($filenames as $filename) {
            $json = FileUtilities::getContents($filename);
            $panels = DonorPanelCollection::fromJson($json);
            yield from $this->read($panels);
        }
    }

    /**
     * @return \Generator<DonorPanel>
     */
    private function read(DonorPanelCollection $panels): \Generator
    {
        foreach ($panels as $panel) {
            yield $panel;
        }
    }

    public function deleteAll(): void
    {
        // careful!
        FileUtilities::unlink($this->path, recursive: true);
    }

    public function save(DonorPanelCollection $panels): void
    {
        $filename = self::getFilename($panels);

        $json = JsonUtilities::jsonEncode($panels, $this->prettyPrint);

        FileUtilities::saveContents($filename, $json);

        if (null !== $this->filenames && 0 === \count($this->getFilenames($panels->state, $panels->id))) {
            $this->filenames = null;
        }
    }

    private function getFilename(DonorPanelCollection $panels): string
    {
        $state = $panels->state;

        Assert::notEmpty($state, 'State cannot be empty');

        $chunkId = $panels->id;

        Assert::greaterThan($chunkId, 0, 'Chunk ID must be greater than 1');

        return \sprintf('%s/%s/chunk%05d.json', $this->path, $state, $chunkId);
    }
}
