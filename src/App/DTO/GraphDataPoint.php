<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\DTO;

class GraphDataPoint implements \JsonSerializable
{
    public function __construct(public \DateTimeImmutable $date, public int|float $value)
    {
    }

    /**
     * @return array{date: string, value: float|int}
     */
    public function jsonSerialize(): array
    {
        return ['date' => $this->date->format('Y-m-d'), 'value' => $this->value];
    }
}
