<?php

return [
    'include' => [

        // css
        'bootstrap4-grid',

        //js
        'jquery',
        'bootstrap4-js',

    ],

    'define' => [

        /*** BOOTSTRAP 3 ***/

        'bootstrap3-css' => [
            'type'    => 'css',
            'version' => '3.3.7',
            'source'  => 'miravel::default/assets/bootstrap3/bootstrap.min.css',
        ],

        'bootstrap3-theme' => [
            'type'    => 'css',
            'version' => '3.3.7',
            'source'  => 'miravel::default/assets/bootstrap3/bootstrap-theme.min.css',
        ],

        'bootstrap3-js' => [
            'type'    => 'js',
            'version' => '3.3.7',
            'source'  => 'miravel::default/assets/bootstrap3/bootstrap.min.js',
        ],

        /*** BOOTSTRAP 4 ***/

        'bootstrap4-css' => [
            'type'    => 'css',
            'version' => '4.0.0-beta.2',
            'source'  => 'miravel::default/assets/bootstrap4/bootstrap.min.css',
        ],

        'bootstrap4-grid' => [
            'type'    => 'css',
            'version' => '4.0.0-beta.2',
            'source'  => 'miravel::default/assets/bootstrap4/bootstrap-grid.min.css',
        ],

        'bootstrap4-reboot' => [
            'type'    => 'css',
            'version' => '4.0.0-beta.2',
            'source'  => 'miravel::default/assets/bootstrap4/bootstrap-reboot.min.css',
        ],

        'bootstrap4-js' => [
            'type'    => 'js',
            'version' => '4.0.0-beta.2',
            'source'  => 'miravel::default/assets/bootstrap4/bootstrap.min.js',
        ],

        'bootstrap4-bundle-js' => [
            'type'    => 'js',
            'version' => '4.0.0-beta.2',
            'source'  => 'miravel::default/assets/bootstrap4/bootstrap.bundle.min.js',
        ],

        /*** JQUERY ***/

        'jquery' => [
            'type'    => 'js',
            'version' => '3.2.1',
            'source'  => 'miravel::default/assets/jquery/jquery-3.2.1.min.js',
        ],

        'jquery-slim' => [
            'type'    => 'js',
            'version' => '3.2.1',
            'source'  => 'miravel::default/assets/jquery/jquery-3.2.1.slim.min.js',
        ],

        /*** MODERNIZR ***/

        'modernizr' => [
            'type'    => 'js',
            'version' => '3.5.0',
            'source'  => 'miravel::default/assets/modernizr/modernizr-3.5.0.min.js',
        ]
    ],
];
