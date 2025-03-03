<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\App\Http\Response;
use CliffordVickrey\Book2024\App\View\View;
use Webmozart\Assert\Assert;

$response = $response ?? new Response();
Assert::isInstanceOf($response, Response::class);
$view = $view ?? new View();
Assert::isInstanceOf($view, View::class);

$view->enqueueJs('map');

?>
<?= $view->partial('search-form', $response); ?>
    <div id="app-map-container" class="app-d3-container">
        <div id="app-map" class="app-d3-plot"></div>
    </div>
<?= $view->partial('export-link', $response); ?>