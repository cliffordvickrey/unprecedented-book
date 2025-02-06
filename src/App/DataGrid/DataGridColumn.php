<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\DataGrid;

class DataGridColumn
{
    public ?string $class = null;
    public DataGridColumnFormat $format = DataGridColumnFormat::none;
    public string $id = '';
    public ?DataGridColumnMeta $meta = null;
    public string $title = '';
    public ?float $width = null;

    public function getClass(): string
    {
        if (null !== $this->class) {
            return $this->class;
        }

        if (DataGridColumnFormat::none === $this->format) {
            return 'text-start';
        }

        return 'text-center';
    }
}
