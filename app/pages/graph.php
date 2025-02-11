<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\App\DTO\DonorProfileQuery;
use CliffordVickrey\Book2024\App\Http\Response;
use CliffordVickrey\Book2024\App\View\View;
use Webmozart\Assert\Assert;

$response = $response ?? new Response();
Assert::isInstanceOf($response, Response::class);
$view = $view ?? new View();
Assert::isInstanceOf($view, View::class);

$view->enqueueJs('graph');

$query = $response->getObject(DonorProfileQuery::class);

?>
<?= $view->partial('search-form', $response); ?>
<div id="app-graph-container">
    <div id="app-graph"></div>
</div>