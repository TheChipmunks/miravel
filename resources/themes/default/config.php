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
];
