<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\DataGrid;

enum DataGridColumnFormat: string
{
    case currency = 'currency';
    case date = 'date';
    case none = 'none';
    case number = 'number';
    case percent = 'percent';
}
