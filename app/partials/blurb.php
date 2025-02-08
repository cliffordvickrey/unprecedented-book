<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\App\Http\Response;
use CliffordVickrey\Book2024\App\View\View;
use CliffordVickrey\Book2024\Common\Enum\CampaignType;
use CliffordVickrey\Book2024\Common\Enum\DonorCharacteristic;
use Webmozart\Assert\Assert;

$response = $response ?? new Response();
Assert::isInstanceOf($response, Response::class);
$view = $view ?? new View();
Assert::isInstanceOf($view, View::class);

$campaignType = $response->getObject(CampaignType::class);
$characteristic = $response->getObject(DonorCharacteristic::class);

?>
<div class="row">
    <div class="col-12">
        <dl>
            <dt class="text-info"><?= $view->htmlEncode($characteristic->getDescription()); ?></dt>
            <dd><?= $characteristic->getBlurb($campaignType); ?></dd>
        </dl>
    </div>
</div>
