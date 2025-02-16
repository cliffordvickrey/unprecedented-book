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
<div class="alert alert-warning mb-0">
    <h5><i class="fa-solid fa-warning"></i> Hold your horses!</h5>
    <p>The data plotted here is <strong>random</strong>. I still have to aggregate FEC info by state/ZCTA.</p>
</div>
<div id="app-map-container" class="app-d3-container">
    <div id="app-map" class="app-d3-plot"></div>
</div>