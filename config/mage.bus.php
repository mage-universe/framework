<?php declare(strict_types=1);

return [
    'paths' => [
        'path' => base_path('src'),
        'pattern' => '/^.*\/Application\/[^\/]+$/',
    ],
    'command' => [
        'middlewares' => []
    ],
    'query' => [
        'middlewares' => []
    ],
    'event' => [
        'middlewares' => []
    ]
];
