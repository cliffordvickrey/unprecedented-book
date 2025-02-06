<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\DataGrid\Grids;

use CliffordVickrey\Book2024\App\DataGrid\DataGrid;
use CliffordVickrey\Book2024\App\DataGrid\DataGridColumn;
use CliffordVickrey\Book2024\App\DataGrid\DataGridColumnFormat;

class DonorProfileGrid extends DataGrid
{
    public function init(array $options = []): void
    {
        $col = new DataGridColumn();
        $col->id = 'characteristic';
        $col->title = 'Type of Donor';
        $col->width = .5;
        $this[] = $col;

        $col = new DataGridColumn();
        $col->id = 'donors';
        $col->title = 'Donors';
        $col->format = DataGridColumnFormat::number;
        $this[] = $col;

        $col = new DataGridColumn();
        $col->id = 'receipts';
        $col->title = 'Receipts';
        $col->format = DataGridColumnFormat::number;
        $this[] = $col;

        $col = new DataGridColumn();
        $col->id = 'amt';
        $col->title = 'Amount';
        $col->format = DataGridColumnFormat::currency;
        $this[] = $col;

        $col = new DataGridColumn();
        $col->id = 'pct';
        $col->title = '%';
        $col->format = DataGridColumnFormat::percent;
        $this[] = $col;
    }
}
