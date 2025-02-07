<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\App\DTO\DonorProfileQuery;
use CliffordVickrey\Book2024\App\Http\Response;
use CliffordVickrey\Book2024\App\View\View;
use CliffordVickrey\Book2024\Common\Enum\State;
use Webmozart\Assert\Assert;

$response = $response ?? new Response();
Assert::isInstanceOf($response, Response::class);
$view = $view ?? new View();
Assert::isInstanceOf($view, View::class);

$query = $response->getObject(DonorProfileQuery::class);

?>
<form method="get" id="app-form">
    <p class="lead">Use this tool to break down contributing behavior of donor types in the American
        electorate. Data is sourced from the FEC Bulk files and FEC API, encompassing itemized receipts
        (> $200) and smaller donations earmarked to candidates through conduit committees like ActBlue
        and WinRed. Donor IDs are fuzzily imputed on the basis of name, jurisdictional, and occupation
        similarity.</p>
    <?= $view->select(
        id: DonorProfileQuery::PARAM_STATE,
        name: DonorProfileQuery::PARAM_STATE,
        label: 'State',
        options: State::getDescriptions(),
        value: $query->state->value,
        hasBlank: false
    ); ?>
</form>
