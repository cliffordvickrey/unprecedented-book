<?php

declare(strict_types=1);

require_once __DIR__.'/../../vendor/autoload.php';

call_user_func(function () {
    $candidates = [
        'harris' => [
            'C00703975', // PCC,
            'C00744946', // HARRIS ACTION FUND,
            'C00838912', // HARRIS VICTORY FUND,
            'C00658476', // DEMOCRATIC GRASSROOTS VICTORY FUND
            'C00010603', // DNC
        ],
        'trump' => [
            'C00828541', // PCC
            'C00867937', // TRUMP 47
            'C00873893', // TRUMP NATIONAL COMMITTEE
            'C00770941', // SAVE AMERICA
            'C00580100', // MAKE AMERICA GREAT AGAIN
            'C00618371', // TRUMP MAKE AMERICA GREAT AGAIN
            'C00855114', // TRUMP BILIRAKIS VICTORY FUND
            'C00003418', // RNC
        ],
    ];
});
