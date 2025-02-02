#!/usr/bin/php
<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\Common\Entity\Combined\DonorPanel;
use CliffordVickrey\Book2024\Common\Repository\DonorPanelRepository;
use CliffordVickrey\Book2024\Common\Service\DonorProfileService;

ini_set('memory_limit', '-1');

chdir(__DIR__);
require_once __DIR__.'/../../vendor/autoload.php';

call_user_func(function () {
    $donorPanelRepository = new DonorPanelRepository();
    $profiler = new DonorProfileService();

    $panels = $donorPanelRepository->get();

    foreach ($panels as $panel) {
        /** @var DonorPanel $panel */
        $profile = $profiler->buildDonorProfile($panel);
        exit($profiler->serializeDonorProfile($profile));
    }
});
