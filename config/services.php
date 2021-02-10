<?php

return [

    'ses' => [

        'region' => env('SES_REGION'),
        'credentials' => [
            'key' => env('SES_KEY'),
            'secret' => env('SES_SECRET_KEY'),
        ],
    ],
];

?>

