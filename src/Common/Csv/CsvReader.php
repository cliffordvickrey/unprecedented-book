<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Csv;

use CliffordVickrey\Book2024\Common\Utilities\JsonUtilities;
use Webmozart\Assert\Assert;

/**
 * @implements \Iterator<int, array<int<0, max>, mixed>>
 */
final class CsvReader extends AbstractResource implements \Iterator
{
    public const string JSON = 'json';

    /** @var array<array-key, mixed>|null */
    private ?array $buffer = null;
    /** @var int<0, max> */
    private int $counter = 0;
    private bool $valid = true;

    public function __construct(string $filename, private readonly string $delimiter = ',')
    {
        parent::__construct($filename);
    }

    public function __destruct()
    {
        $this->close();
    }

    public function next(): void
    {
        if (null === $this->buffer) {
            $this->current();
        }

        $this->buffer = null;
        ++$this->counter;
    }

    /**
     * @return array<array-key, mixed>
     */
    public function current(): array
    {
        if (null !== $this->buffer) {
            return $this->buffer;
        }

        $row = $this->readRow();

        if (false === $row) {
            Assert::true(feof($this->getResource()), 'Unable to read to end of file');
            $this->valid = false;
            $row = [];
        }

        $this->buffer = $row;

        return $row;
    }

    /**
     * @return array<array-key, mixed>|false
     */
    private function readRow(): array|false
    {
        $resource = $this->getResource();

        if (',' === $this->delimiter) {
            return fgetcsv($resource);
        }

        $ln = fgets($resource);

        if (!\is_string($ln)) {
            return false;
        }

        if (self::JSON === $this->delimiter) {
            return JsonUtilities::jsonDecode($ln);
        }

        return str_replace('\\', '', explode('|', trim($ln)));
    }

    public function valid(): bool
    {
        $this->current();

        return $this->valid;
    }

    public function key(): int
    {
        return $this->counter;
    }

    public function rewind(): void
    {
        $this->close();
    }

    protected function doClose(): void
    {
        $this->buffer = null;
        $this->counter = 0;
        $this->valid = true;
    }
}
