<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Csv;

use Webmozart\Assert\Assert;

final class CsvWriter extends AbstractResource
{
    public function __construct(string $filename, string $mode = 'w')
    {
        parent::__construct($filename, $mode);
    }

    public function toReader(): CsvReader
    {
        return new CsvReader($this->filename);
    }

    private static function makeScalar(mixed $val): int|float|string|null
    {
        if (\is_bool($val)) {
            return $val ? 1 : 0;
        }

        if (null === $val || \is_scalar($val)) {
            return $val;
        }

        if (\is_array($val)) {
            return implode('|', $val);
        }

        return null;
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public function write(array $data): void
    {
        /** @var array<int|string, float|int|string|null> $filtered */
        $filtered = array_map(self::makeScalar(...), $data);
        $bytes = fputcsv($this->getResource(), $filtered);
        Assert::integer($bytes, 'Could not write to CSV file');
    }
}
