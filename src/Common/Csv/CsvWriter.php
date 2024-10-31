<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Csv;

use Webmozart\Assert\Assert;

final class CsvWriter extends AbstractResource
{
    public function __construct(string $filename)
    {
        parent::__construct($filename, 'w');
    }

    private static function makeScalar(mixed $val): bool|int|float|string|null
    {
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
        $filtered = array_map(self::makeScalar(...), $data);
        $bytes = fputcsv($this->getResource(), $filtered);
        Assert::integer($bytes, 'Could not write to CSV file');
    }
}
