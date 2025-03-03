<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\App\View\View;
use Webmozart\Assert\Assert;

$view = $view ?? new View();
Assert::isInstanceOf($view, View::class);

$view->enqueueJs('export');

?>
<a href="#" id="app-export"><i class="fa-solid fa-file-csv"></i> Export</a>