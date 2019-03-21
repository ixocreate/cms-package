<?php

return [
    'cms' => [
        'driver' => \Ixocreate\Cache\Driver\FilesystemDriver::class,
        'options' => [
            'directory' => getcwd() . '/data/cache/cms'
        ],
    ]
];
