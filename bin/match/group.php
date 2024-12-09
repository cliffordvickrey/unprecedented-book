<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\Common\Csv\CsvReader;
use CliffordVickrey\Book2024\Common\Entity\Combined\Donor;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;

require_once __DIR__ . '/../../vendor/autoload.php';

call_user_func(function () {
    $reader = new CsvReader(__DIR__ . '/../../data/csv/_unique-donors.csv');
    $headers = array_map(strval(...), array_map(CastingUtilities::toString(...), $reader->current()));
    $reader->next();

    while ($reader->valid()) {
        $donor = Donor::__set_state(array_combine($headers, $reader->current()));
        printf('%s%s', $donor->getSlug(), PHP_EOL);
        $reader->next();
    }

    $reader->close();

});
