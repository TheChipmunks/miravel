<?php

return [
    'css' => [

        // the "main-style" bundle
        'main-style' => [
            'src' => [
                'assets/vendor/bootstrap4/bootstrap.css',
                'assets/vendor/font-awesome/web-fonts-with-css/scss/fontawesome.scss',

                // include styles from each element, layout and skin
                'skins',
                'layouts',
                'elements',
            ],

            // filters to apply to concatenated file contents
            'filters' => [
                'source-maps' => !app()->environment('production'),
                'minify'      => app()->environment('production'),
            ],

            'dest' => 'styles.css'
        ],
    ],

    'js' => [

        // the "main-script" bundle
        'main-script' => [
            'src' => [
                'assets/vendor/jquery/jquery-3.2.1.slim.js',
                'assets/vendor/popper/popper.js',
                'assets/vendor/bootstrap4/bootstrap.js',
                'assets/vendor/modernizr/modernizr-3.5.0.min.js',

                // include scripts from each element, layout and skin
                'skins',
                'layouts',
                'elements',
            ],

            // filters to apply to concatenated file contents
            'filters' => [
                'source-maps' => !app()->environment('production'),
                'minify'      => app()->environment('production'),
            ],

            'dest' => 'scripts.js'
        ],

    ],

    'copy' => [
        'assets/src/browserconfig.xml'                           => public_path('browserconfig.xml'),
        'assets/src/site.webmanifest'                            => public_path('public/site.webmanifest'),

        'assets/src/images'                                      => 'images',

        'assets/vendor/font-awesome/web-fonts-with-css/webfonts' => public_path('fonts/font-awesome'),
    ],
];
