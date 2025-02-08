<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\App\Http\Response;
use CliffordVickrey\Book2024\App\View\View;
use Webmozart\Assert\Assert;

$response = $response ?? new Response();
Assert::isInstanceOf($response, Response::class);
$view = $view ?? new View();
Assert::isInstanceOf($view, View::class);

$id = $view->htmlEncode($response->getAttribute('id', ''));
$label = $view->htmlEncode($response->getAttribute('label', ''));

?>
<div class="accordion-item">
    <h2 class="accordion-header" id="app-accordion-heading-<?= $id; ?>">
        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                data-bs-target="#app-accordion-collapse-<?= $id; ?>" aria-expanded="true"
                aria-controls="app-accordion-collapse-<?= $id; ?>">
            <?= $label; ?>
        </button>
    </h2>
    <div id="app-accordion-collapse-<?= $id; ?>" class="accordion-collapse collapse show"
         aria-labelledby="app-accordion-heading-<?= $id; ?>">
        <div class="accordion-body">
            <div class="container-fluid">
                <!-- pane content -->
                <?= $response->getAttribute('content', ''); ?>
                <!-- /pane content -->
            </div>
        </div>
    </div>
</div>
