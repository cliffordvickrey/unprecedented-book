<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\DTO;

use CliffordVickrey\Book2024\App\Contract\AbstractCollection;

/**
 * @extends AbstractCollection<int, GraphDataPoint>
 */
class GraphData extends AbstractCollection implements \JsonSerializable
{
    public function __construct(public GraphType $graphType = GraphType::amount)
    {
    }

    /**
     * @return array{
     *     title: string,
     *     isDollarAmount: bool,
     *     dataPoints: list<GraphDataPoint>
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'title' => $this->graphType->getTitle(),
            'isDollarAmount' => $this->graphType->isDollarAmount(),
            'dataPoints' => array_values($this->data),
        ];
    }
}
