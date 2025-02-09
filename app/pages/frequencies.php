<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\App\DataGrid\Grids\DonorProfileGrid;
use CliffordVickrey\Book2024\App\DataGrid\Grids\DonorProfileGridCycle2016;
use CliffordVickrey\Book2024\App\DataGrid\Grids\DonorProfileGridCycle2020;
use CliffordVickrey\Book2024\App\DataGrid\Grids\DonorProfileGridCycle2024;
use CliffordVickrey\Book2024\App\DataGrid\Grids\DonorProfileGridDonor;
use CliffordVickrey\Book2024\App\DTO\DonorProfileQuery;
use CliffordVickrey\Book2024\App\Http\Response;
use CliffordVickrey\Book2024\App\View\View;
use Webmozart\Assert\Assert;

$response = $response ?? new Response();
Assert::isInstanceOf($response, Response::class);
$view = $view ?? new View();
Assert::isInstanceOf($view, View::class);

$query = $response->getObject(DonorProfileQuery::class);

?>
<?= $view->partial('search-form', $response); ?>
<?php if ($query->campaignType): ?>
    <div class="container-fluid gx-0">
        <div class="row">
            <div class="col-12">
                <div class="accordion">
                    <?= $view->partial(
                        'profile',
                        [DonorProfileGrid::class => $response->getObject(DonorProfileGridDonor::class)]
                    ); ?>
                    <?= $view->partial(
                        'profile',
                        [DonorProfileGrid::class => $response->getObject(DonorProfileGridCycle2024::class)]
                    ); ?>
                    <?= $view->partial(
                        'profile',
                        [DonorProfileGrid::class => $response->getObject(DonorProfileGridCycle2020::class)]
                    ); ?>
                    <?= $view->partial(
                        'profile',
                        [DonorProfileGrid::class => $response->getObject(DonorProfileGridCycle2016::class)]
                    ); ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>