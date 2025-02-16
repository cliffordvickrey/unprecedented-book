<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\DTO;

use CliffordVickrey\Book2024\App\Contract\AbstractCollection;

/**
 * @extends AbstractCollection<int, TDataPoint>
 *
 * @template TDataPoint
 */
abstract class AbstractD3Data extends AbstractCollection implements \JsonSerializable
{
    public function __construct(
        public GraphType $graphType = GraphType::amount,
        public GraphColor $color = GraphColor::blue,
    ) {
    }

    /**
     * @return array{
     *     title: string,
     *     color: string,
     *     isDollarAmount: bool,
     *     dataPoints: list<TDataPoint>
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'title' => $this->graphType->getTitle(),
            'color' => $this->color->value,
            'isDollarAmount' => $this->graphType->isDollarAmount(),
            'dataPoints' => array_values($this->data),
        ];
    }
}
