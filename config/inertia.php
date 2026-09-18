<?php

declare(strict_types=1);

return [
    'pages' => [
        'ensure_pages_exist' => false,
        'paths' => [
            resource_path('js/Pages'),
        ],
        'extensions' => ['vue'],
    ],

    'testing' => [
        'ensure_pages_exist' => true,
    ],

    'ssr' => [
        'enabled' => false,
    ],
];
