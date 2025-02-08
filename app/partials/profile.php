<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\App\DataGrid\Grids\DonorProfileGrid;
use CliffordVickrey\Book2024\App\Http\Response;
use CliffordVickrey\Book2024\App\View\View;
use Webmozart\Assert\Assert;

$response = $response ?? new Response();
Assert::isInstanceOf($response, Response::class);
$view = $view ?? new View();
Assert::isInstanceOf($view, View::class);

$grid = $response->getObject(DonorProfileGrid::class);
$genre = $grid->getGenre();

echo $view->panel(
    $genre->value,
    $genre->getDescription(),
    $view->dataGrid($grid)
);
