<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\App\DataGrid\DataGrid;
use CliffordVickrey\Book2024\App\DataGrid\DataGridColumnFormat;
use CliffordVickrey\Book2024\App\Http\Response;
use CliffordVickrey\Book2024\App\View\View;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;
use Webmozart\Assert\Assert;

$response = $response ?? new Response();
Assert::isInstanceOf($response, Response::class);
$view = $view ?? new View();
Assert::isInstanceOf($view, View::class);

$grid = $response->getObject(DataGrid::class);

$metaColSpans = $grid->getMetaColSpans();
$colWidths = $grid->getColWidths();
$values = $grid->getValues();

?>
<div class="table-responsive">
    <table class="table table-sm table-bordered">
        <colgroup>
            <?php foreach ($colWidths as $colWidth): ?>
                <col style="width: <?= $view->htmlEncode($view->formatPercent($colWidth)); ?>;">
            <?php endforeach; ?>
        </colgroup>
        <thead>
        <?php if (0 !== count($metaColSpans)): ?>
            <tr>
                <?php foreach ($metaColSpans as $metaColspan): ?>
                    <th class="text-center"<?= $metaColspan['colSpan'] > 1
                        ? sprintf(' colspan="%d"', $metaColspan['colSpan'])
                        : '';
                    ?>><?= $view->htmlEncode($metaColspan['title']); ?></th>
                <?php endforeach; ?>
            </tr>
        <?php endif; ?>
        <tr>
            <?php foreach ($grid as $column): ?>
                <th class="text-center"><?= $view->htmlEncode($column->title); ?></th>
            <?php endforeach; ?>
        </tr>
        </thead>
        <tbody>
        <?php if (0 === count($values)): ?>
            <tr>
                <td class="text-muted text-center"<?= count($grid) > 1
                    ? sprintf(' colspan="%d"', count($grid))
                    : '';
            ?>>There is no data to display.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($values as $row): ?>
                <tr>
                    <?php foreach ($row as $columnId => $value):
                        $col = $grid[$columnId];
                        $class = $col->getClass();

                        $formattedValue = $value;

                        if (null !== $value && '' !== $value) {
                            switch ($col->format) {
                                case DataGridColumnFormat::currency:
                                    $formattedValue = $view->formatCurrency($value);
                                    break;
                                case DataGridColumnFormat::date:
                                    $valueDt = CastingUtilities::toDateTime($value);

                                    if (null === $valueDt) {
                                        $formattedValue = '';
                                    } else {
                                        $formattedValue = $view->formatDate($valueDt);
                                    }

                                    break;
                                case DataGridColumnFormat::none:
                                    $formattedValue = (string) CastingUtilities::toString($value);
                                    break;
                                case DataGridColumnFormat::number:
                                    $formattedValue = $view->formatNumber($value);
                                    break;
                                case DataGridColumnFormat::percent:
                                    $formattedValue = $view->formatPercent($value);
                                    break;
                            }
                        }

                        ?>
                        <td class="<?= $view->htmlEncode($class); ?>"><?= $view->htmlEncode($formattedValue); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach;
        endif; ?>
        </tbody>
    </table>
</div>

