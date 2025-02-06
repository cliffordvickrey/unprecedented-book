<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\DataGrid;

use CliffordVickrey\Book2024\Common\Exception\BookLogicException;
use CliffordVickrey\Book2024\Common\Exception\BookUnexpectedValueException;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;

/**
 * @extends \CliffordVickrey\Book2024\App\Contract\AbstractCollection<string, DataGridColumn>
 */
abstract class DataGrid extends \CliffordVickrey\Book2024\App\Contract\AbstractCollection
{
    /** @var list<array<string, mixed>> */
    private array $values = [];

    /**
     * @param array<string, mixed> $options
     */
    final public function __construct(array $options = [])
    {
        $this->init($options);
    }

    /**
     * @param array<string, mixed> $options
     */
    abstract public function init(array $options = []): void;

    /**
     * @return list<array<string, mixed>>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @param array<array-key, mixed> $values
     */
    public function setValues(array $values): void
    {
        if (!array_is_list($values)) {
            $values = [$values];
        }

        $formattedRows = [];

        $template = array_combine($this->getColumnIds(), array_fill(0, \count($this), null));

        foreach ($values as $row) {
            $formattedRow = $template;

            if (!is_iterable($row)) {
                throw new BookUnexpectedValueException('Expected associative array or array of associative arrays');
            }

            foreach ($row as $k => $v) {
                $k = (string) CastingUtilities::toString($k);

                if (\array_key_exists($k, $formattedRow)) {
                    $formattedRow[$k] = $v;
                }
            }

            $formattedRows[] = $formattedRow;
        }

        $this->values = $formattedRows;
    }

    /**
     * @return string[]
     */
    public function getColumnIds(): array
    {
        return array_values(array_map(static fn (DataGridColumn $column) => $column->id, $this->data));
    }

    /**
     * @return list<array{title: string, colSpan: int}>
     */
    public function getMetaColSpans(): array
    {
        $reduced = array_reduce($this->data, static function (array $carry, DataGridColumn $column): array {
            /** @var array{current: string|null, colSpans: list<array{title: string, colSpan: int}>} $carry */
            if (null === $column->meta) {
                return $carry;
            } elseif ($column->meta->title === $carry['current']) {
                ++$carry['colSpans'][array_key_last($carry['colSpans'])]['colSpan'];
            } else {
                $carry['current'] = $column->meta->title;
                $carry['colSpans'][] = ['title' => $column->meta->title, 'colSpan' => 1];
            }

            return $carry;
        }, [
            'current' => null,
            'colSpans' => [],
        ]);

        return array_values($reduced['colSpans']);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (null === $offset) {
            $offset = $value->id;

            if (isset($this[$offset])) {
                $msg = \sprintf('Column with ID "%s" already exists', $offset);
                throw new BookLogicException($msg);
            }
        }

        parent::offsetSet($offset, $value);
    }

    /**
     * @return list<float>
     */
    public function getColWidths(): array
    {
        $count = \count($this);

        if (0 === $count) {
            return [];
        }

        $reduced = array_reduce($this->data, static function (array $carry, DataGridColumn $column): array {
            /** @var array{count: int, sum: float} $carry */
            if (null === $column->width) {
                return $carry;
            }

            ++$carry['count'];
            $carry['sum'] += $column->width;

            return $carry;
        }, ['count' => 0, 'sum' => 0.0]);

        $remainingCount = $count - $reduced['count'];

        if (0 === $remainingCount) {
            $remainingWidth = 0.0;
        } else {
            $remainingWidth = round((1.0 - $reduced['sum']) / $remainingCount, 4);
        }

        $widths = [];

        foreach ($this->data as $column) {
            $widths[] = $column->width ?? $remainingWidth;
        }

        return $widths;
    }
}
