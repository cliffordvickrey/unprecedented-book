<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\DataGrid\Grids;

use CliffordVickrey\Book2024\App\DataGrid\DataGrid;
use CliffordVickrey\Book2024\App\DataGrid\DataGridColumn;
use CliffordVickrey\Book2024\App\DataGrid\DataGridColumnFormat;
use CliffordVickrey\Book2024\Common\Entity\Report\DonorReport;
use CliffordVickrey\Book2024\Common\Entity\Report\DonorReportRow;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristicGenre;
use CliffordVickrey\Book2024\Common\Exception\BookUnexpectedValueException;
use CliffordVickrey\Book2024\Common\Utilities\StringUtilities;
use Webmozart\Assert\Assert;

abstract class DonorProfileGrid extends DataGrid
{
    /**
     * @return list<DonorProfileGrid>
     */
    public static function collectChildren(): array
    {
        return array_map(
            static function (DonorCharacteristicGenre $genre): DonorProfileGrid {
                $classStr = self::class.StringUtilities::snakeCaseToPascalCase($genre->value);

                Assert::classExists($classStr);

                if (!is_subclass_of($classStr, self::class)) {
                    $msg = \sprintf('Expected subclass of %s; got %s', self::class, $classStr);
                    throw new BookUnexpectedValueException($msg);
                }

                return new $classStr();
            },
            DonorCharacteristicGenre::cases(),
        );
    }

    public function setReport(DonorReport $report): void
    {
        $genre = $this->getGenre();

        $filteredReport = $report->withFilter(fn (DonorReportRow $row) => $row->characteristic->getGenre() === $genre);

        $this->setValues($filteredReport->toRecords());
    }

    abstract public function getGenre(): DonorCharacteristicGenre;

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
