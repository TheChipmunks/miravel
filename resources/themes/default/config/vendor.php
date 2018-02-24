<?php

return [
    'css' => [
        'bootstrap4-css' => [
            'version' => '4.0.0-beta.2',
            // relative to theme directory
            'source'  => 'vendor/bootstrap4/bootstrap.min.css',
        ],

        'bootstrap4-grid' => [
            'version' => '4.0.0-beta.2',
            // relative to theme directory
            'source'  => 'vendor/bootstrap4/bootstrap-grid.min.css',
        ],

        'bootstrap4-reboot' => [
            'version' => '4.0.0-beta.2',
            // paths starting with forward slash (or disk letter on Windows)
            // are absolute to the filesystem
            'source'  => '/var/www/node_modules/bootstrap/scss/bootstrap-reboot.scss',
            // 'source' => 'vendor/bootstrap4/bootstrap-reboot.min.css,
        ],

        'font-awesome' => [
            'version' => '4.7.0',
            'source'  => 'vendor/font-awesome/font-awesome.scss',
        ]
    ],

    'js' => [
        'bootstrap4-js' => [
            'version' => '4.0.0-beta.2',
            // relative to theme directory
            'source'  => 'vendor/bootstrap4/bootstrap.min.js',
        ],

        'jquery-slim' => [
            'version' => '3.2.1',
            // relative to theme directory
            'source'  => 'vendor/jquery/jquery-3.2.1.slim.min.js',
        ],

        'modernizr' => [
            'version' => '3.0.0',
            // pull from url and cache
            'source'  => 'https://cdn.jsdelivr.net/npm/modernizr-prebuilt@3.0.0-pre/dist/modernizr-build.min.js',
        ],

        'tooltip-js' => [
            'version' => 'v1.12.9',
            // pull from url and cache
            'source'  => 'https://unpkg.com/tooltip.js/dist/umd/tooltip.min.js',
        ],
    ],
];
