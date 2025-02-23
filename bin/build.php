<?php

declare(strict_types=1);

call_user_func(function () {
    $scripts = [
        __DIR__.'/fec-bulk/concatenate-bulk-downloads.php',
        __DIR__.'/fec-bulk/build-aggregates.php',
        __DIR__.'/fec-api/parse-receipts.php',
        __DIR__.'/match/group.php',
        __DIR__.'/match/chunk-groups.php',
        __DIR__.'/match/match.php',
        __DIR__.'/match/merge.php',
        __DIR__.'/panel/chunk-receipts.php',
        __DIR__.'/panel/panelize.php',
    ];

    foreach ($scripts as $i => $script) {
        $path = realpath($script);

        if (false === $path) {
            throw new UnexpectedValueException("Could not load PHP script: $script");
        }

        printf('Running script #%d (%s)...%s', $i + 1, $path, \PHP_EOL);
        include $path;
    }
});
