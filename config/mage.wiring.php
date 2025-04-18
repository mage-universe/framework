<?php declare(strict_types=1);

return [
    'bindings' => [
        'path' => base_path('src'),
        'pattern' => '/^.*\/Infrastructure\/[^\/]+$/',
    ],
    'commands' => [
        'path' => base_path('src'),
        'pattern' => '/^.*\/Infrastructure\/Console\/[^\/]+$/',
    ],
    'routes' => [
        'path' => base_path('src'),
        'pattern' => '/^.*\/Infrastructure\/Routes\/[^\/]+$/',
    ],
    'migrations' => [
        'path' => base_path('src'),
        'pattern' => '/^.*\/Infrastructure\/Persistence\/(?:\w+\/)*Migrations\/[^\/]+$/',
    ]
];
