<?php

declare(strict_types=1);

return [
    'default' => 'sqlite',
    'connections' => [
        'sqlite' => [
            'type' => 'sqlite',
            'database' => root_path() . 'runtime/drama_generator.db',
            'prefix' => '',
        ],
    ],
];
