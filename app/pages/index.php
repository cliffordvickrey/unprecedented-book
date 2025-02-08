<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\App\DTO\DonorProfileQuery;
use CliffordVickrey\Book2024\App\Http\Response;
use CliffordVickrey\Book2024\App\View\View;
use CliffordVickrey\Book2024\Common\Enum\CampaignType;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;
use CliffordVickrey\Book2024\Common\Enum\State;
use Webmozart\Assert\Assert;

$response = $response ?? new Response();
Assert::isInstanceOf($response, Response::class);
$view = $view ?? new View();
Assert::isInstanceOf($view, View::class);

$query = $response->getObject(DonorProfileQuery::class);

?>
<div class="container-fluid">
    <form method="get" id="app-search-form">
        <div class="row">
            <div class="col-12">
                <p class="lead">Use this tool to break down contributing behavior of donor types in the American
                    electorate. Data is sourced from the FEC Bulk files and FEC API, encompassing itemized receipts
                    (> $200) and smaller donations earmarked to candidates through conduit committees like ActBlue
                    and WinRed. Donor IDs are fuzzily imputed on the basis of name, jurisdictional, and occupational
                    similarity.</p>
            </div>
        </div>
        <div class="row py-1">
            <div class="col-12 col-lg-6">
                <?= $view->select(
                    id: DonorProfileQuery::PARAM_CAMPAIGN_TYPE,
                    name: DonorProfileQuery::PARAM_CAMPAIGN_TYPE,
                    label: 'Campaign',
                    options: CampaignType::getDescriptions(),
                    value: $query->campaignType?->value
                ); ?>
            </div>
            <?php if ($query->campaignType): ?>
                <div id="app-clear-button" class="col-12 col-lg-6">
                    <button class="btn btn-secondary">Clear All</button>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($query->campaignType): ?>
            <div class="row py-3">
                <div class="col-12">
                    <?php switch ($query->campaignType):
                        case CampaignType::kamala_harris: ?>
                            Donors who gave to <span class="text-info">Kamala Harris</span> (campaign, joint
                            fundraising, and independent expenditure committees that targeted her candidacy) between
                            2024-07-21 and 2024-10-16.
                            <?php break;
                        case CampaignType::joe_biden: ?>
                            Donors who gave to <span class="text-info">Joe Biden</span> (campaign, joint fundraising
                            , and independent expenditure committees that targeted his candidacy) between 2023-01-01
                            and 2024-07-20.
                            <?php break;
                        default: ?>
                            Donors who gave to <span class="text-info">Donald Trump</span> (campaign, joint
                            fundraising, and independent expenditure committees that targeted his candidacy) between
                            2022-11-15 and 2024-10-16.
                        <?php endswitch; ?>
                </div>
            </div>
            <div class="row py-1">
                <div class="col-12 col-lg-6">
                    <?= $view->select(
                        id: 'app-state-filter',
                        name: DonorProfileQuery::PARAM_STATE,
                        label: 'State',
                        options: State::getDescriptions(),
                        value: $query->state->value,
                        hasBlank: false
                    ); ?>
                </div>
            </div>
            <div class="row py-1">
                <div class="col-12 col-lg-6">
                    <?= $view->select(
                        id: 'app-characteristic-filter-a',
                        name: sprintf('%s[]', DonorProfileQuery::PARAM_CHARACTERISTIC),
                        label: 'Characteristic',
                        options: DonorCharacteristic::getDescriptions(),
                        value: $query->characteristicA?->value
                    ); ?>
                </div>
            </div>
            <?php if ($query->characteristicA): ?>
                <div class="row py-1">
                    <div class="col-12 col-lg-6">
                        <?= $view->select(
                            id: 'app-characteristic-filter-b',
                            name: sprintf('%s[]', DonorProfileQuery::PARAM_CHARACTERISTIC),
                            label: 'Characteristic',
                            options: DonorCharacteristic::getDescriptions($query->characteristicA),
                            value: $query->characteristicB?->value
                        ); ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </form>
    <?php if ($query->campaignType): ?>
        <?= $view->partial('profiles', $response); ?>
    <?php endif; ?>
</div>
