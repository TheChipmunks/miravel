<?php

return [
    'extends' => 'test-parent',

    'build' => [

        // main css bundle
        'css' => [
            'main-style' => [
                'src' => [
                    'assets/src/style.scss',
                ],

                // will place the result file to storage/miravel/build/test-child/assets/dist/style.css
                'dist' => 'assets/dist/style.css',
            ]
        ],


        // main js bundle
        'js' => [
            'main-script' => [
                'src' => [
                    'assets/src/script.js',
                ],

                // will place the result file to storage/miravel/build/test-child/assets/dist/script.js
                'dist' => 'assets/dist/script.js',
            ]
        ],


    ],

    'publish' => [
        // each path is looked up first in
        // - storage/miravel/build/test-child

        // if not found (e.g. user did not run build), then in
        // - vendor/miravel/miravel/resources/themes/test-child

        'assets/dist/style.css' => public_path('styles.css'),

        'assets/dist/script.js' => public_path('scripts.js'),
    ]
];
