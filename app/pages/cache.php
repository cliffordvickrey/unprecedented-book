<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\App\Controller\CacheController;
use CliffordVickrey\Book2024\App\Http\Response;
use CliffordVickrey\Book2024\App\View\View;
use Webmozart\Assert\Assert;

$response = $response ?? new Response();
Assert::isInstanceOf($response, Response::class);
$view = $view ?? new View();
Assert::isInstanceOf($view, View::class);

$cacheCleared = $response->getAttribute(CacheController::CACHE_CLEARED, false);
$free = $response->getAttribute(CacheController::MEMORY_FREE, '');
$total = $response->getAttribute(CacheController::MEMORY_TOTAL, '');

?>
<?php if ($cacheCleared): ?>
    <div class="alert alert-success">
        Cache cleared successfully!
    </div>
<?php endif; ?>
<h5>Cache Info</h5>
<dl>
    <dt>Total</dt>
    <dd><?= $view->htmlEncode($total); ?></dd>
    <dt>Free</dt>
    <dd><?= $view->htmlEncode($free); ?></dd>
</dl>
<form action="./?action=cache" method="post" class="mb-2">
    <button class="btn btn-primary" type="submit">Clear the Cache</button>
</form>
<a href="./" style="text-decoration: none"><i class="fa-solid fa-arrow-left"></i> Go back</a>