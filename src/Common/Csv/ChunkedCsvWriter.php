<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Csv;

class ChunkedCsvWriter
{
    public const int MAX_RECORDS = 100000;

    private int $counter = 0;
    /** @var array<string, list<array<array-key, mixed>>> */
    private array $records = [];
    /** @var array<string, bool> */
    private array $openedFiles = [];

    public function __construct(private readonly int $maxRecords = self::MAX_RECORDS)
    {
    }

    /**
     * @param array<array-key, mixed> $row
     */
    public function push(string $filename, array $row): void
    {
        if (!isset($this->records[$filename])) {
            $this->records[$filename] = [$row];
        } else {
            $this->records[$filename][] = $row;
        }

        ++$this->counter;

        if ($this->counter <= $this->maxRecords) {
            return;
        }

        $this->flush();
    }

    public function flush(): void
    {
        printf('Writing %d CSVs (%d rows)%s', \count($this->records), $this->counter, \PHP_EOL);

        foreach ($this->records as $filename => $rows) {
            $mode = isset($this->openedFiles[$filename]) ? 'a' : 'w';

            $this->openedFiles[$filename] = true;

            $writer = new CsvWriter($filename, $mode);
            array_walk($rows, static fn (array $row) => $writer->write($row));
            $writer->close();
        }

        $this->counter = 0;
        $this->records = [];
    }
}
