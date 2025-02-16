<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\DTO;

class MapDataPoint implements \JsonSerializable
{
    public function __construct(public string $jurisdiction, public int|float $value)
    {
    }

    /**
     * @return array{jurisdiction: string, value: float|int}
     */
    public function jsonSerialize(): array
    {
        return ['jurisdiction' => $this->jurisdiction, 'value' => $this->value];
    }
}
