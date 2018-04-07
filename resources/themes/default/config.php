<?php

return [
    'extends' => null,

    'publish' => [
        // assets that were built from sources and can be re-built with miravel:build
        'assets/dist/styles.css'                                 => public_path('styles.css'),
        'assets/dist/scripts.js'                                 => public_path('scripts.js'),

        // other resources
        'assets/src/browserconfig.xml'                           => public_path('browserconfig.xml'),
        'assets/src/site.webmanifest'                            => public_path('site.webmanifest'),
        'assets/src/images'                                      => 'images',
        'assets/vendor/font-awesome/web-fonts-with-css/webfonts' => public_path('fonts/font-awesome'),
    ],
    
    'build' => [
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
            
        ]
    ]
];
