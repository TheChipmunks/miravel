<?php

return [
    'css' => [
        // will be published to public/miravel/{theme-name}/styles.css
        'styles' => [
            'source' => [
                // relative to theme directory
                'assets/css/_variables.scss',

                // taken from vendor sources declared in vendor.php
                'vendor::bootstrap4-css',
                'vendor::bootstrap4-grid',
                'vendor::bootstrap4-reboot',
                'vendor::font-awesome',

                // relative to theme directory
                'assets/css/styles.scss',
                'skins/default/skin.scss',

                // if source is a directory, look recursively for all type
                // of source files in this directory
                'layouts',

                // any source may also have options
                'elements' => [
                    'glob'    => '**/*.{scss,sass,css}',
                    'except'  => 'dont-include.scss',
                    'filters' => ['postcss', 'autoprefixer', 'processImageUrls']
                ],
            ],

            'minify'    => app()->environment('production'),
            'sourcemap' => !app()->environment('production'),
        ],
    ],

    'js'  => [
        // will be published to public/miravel/{theme-name}/js/head.js
        'js/head' => [
            'source' => [
                'assets/js/head.js',
            ]
        ],

        // will be published to public/dist/top.js
        'top' => [
            'source'        => [
                'assets/js/top.js',
            ],

            // ~ means project root directory
            'destination'   => '~/public/dist/top.js',
        ],

        // will be published to /tmp/bottom.js
        'bottom' => [
            'source' => [
                // taken from vendor sources declared in vendor.php
                'vendor::jquery-slim',
                'vendor::bootstrap4-js',
                'vendor::modernizr',
                'vendor::tooltip-js',

                // asset:: means look in 'assets' folder of this theme
                'assets/js/scripts.js',

                // es5 and es6 files must be transpiled
                'assets/js/scripts6.es6',

                // another way to apply transpiler
                'assets/js/transpiled.js' => [
                    'filters' => ['transpile-es6']
                ],

                // if source is a directory, look recursively for all type
                // of source files in this directory
                'layouts',
                'elements',
            ],

            // 1. Paths starting with forward slash (or disk letter on Windows)
            // are absolute to filesystem.
            // 2. If destination is an existing directory, we build file name by
            // appending '.js' to asset name
            // so the output file here is /tmp/bottom.js
            'destination'     => '/tmp',

            'minify'          => app()->environment('production'),
            'sourcemap'       => !app()->environment('production'),
        ],
    ],

    'copy' => [

        // if no destination is specified, files are copied to the same path
        // relative to public/miravel/{theme-name}

        // =>  public/miravel/{theme-name}/assets/browserconfig.xml
        'assets/browserconfig.xml',

        /*****************************************/

        // if we specify a string, it is a destination

        // will be copied to public/miravel/{theme-name}/images
        'assets/img'                => 'images',
        // will be copied to public/fonts/FontAwesome
        'vendor/font-awesome/fonts' => '~/public/fonts/FontAwesome',

        /*****************************************/

        // if we specify an array, it is options

        // =>  public/miravel/{theme-name}/assets/site.webmanifest
        'assets/site.webmanifest' => [
            'destination' => '~/public/site.webmanifest',
            'filters'     => ['processImageUrls']
        ],
    ]
];
