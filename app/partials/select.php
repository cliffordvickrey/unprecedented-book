<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\App\Http\Response;
use CliffordVickrey\Book2024\App\View\View;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;
use Webmozart\Assert\Assert;

$response = $response ?? new Response();
Assert::isInstanceOf($response, Response::class);
$view = $view ?? new View();
Assert::isInstanceOf($view, View::class);

$hasBlank = $response->getAttribute('hasBlank', false);
$id = $response->getAttribute('id', '');
$name = $response->getAttribute('name', '');
$label = $response->getAttribute('label', '');

$options = $hasBlank ? ['' => ''] : [];

$options += $response->getAttribute('options', []);
$value = (string) CastingUtilities::toString(isset($response['value']) ? $response['value'] : null);

$renderOption = function (int|string $key, mixed $optionValue) use ($value, $view): void {
    $selected = $key === $value ? ' selected="selected"' : ''; ?>
    <option value="<?= $view->htmlEncode($key); ?>"<?= $selected; ?>><?= $view->htmlEncode($optionValue); ?></option>
    <?php
}

?>
<div class="input-group">
    <span class="input-group-text" id="<?= $view->htmlEncode($id); ?>-addon"><?= $view->htmlEncode($label); ?></span>
    <select id="<?= $view->htmlEncode($id); ?>" class="form-select" autocomplete="off"
            aria-label="<?= $view->htmlEncode($label); ?>" aria-describedby="<?= $view->htmlEncode($id); ?>-addon"
            name="<?= $view->htmlEncode($name); ?>">
        <?php foreach ($options as $k => $v):
            if (is_array($v)): ?>
                <optgroup label="<?= $view->htmlEncode($k); ?>">
                    <?php foreach ($v as $optGroupK => $optGroupV):
                        $renderOption($optGroupK, $optGroupV);
                    endforeach; ?>
                </optgroup>
            <?php else:

                $renderOption($k, $v);

            endif;
        endforeach; ?>
    </select>
</div>
