<?php

return [
    'html' => [
        'doctype' => 'html',

        'html_tag_attributes' => [
            'class' => 'no-js',
        ],

        'body_tag_attributes' => [
            //
        ],

        'meta_tags' => [
            ['charset' => 'utf-8'],
            ['http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge'],
            ['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no'],
        ],
    ],

    'template_file_extensions' => ['php', 'blade.php', 'phtml'],

    'template_file_name'       => 'view',

    'blade_directive_map' => [

        // the right part is variable, change to your liking
        // note that the default version is a convention used in third party themes
        // once you change a directive here, you'll have to search and replace its
        // occurrences inside the view files, templates etc.

        // the left part is the default value. Do not change it.

        'themeinclude' => 'themeinclude',
        'themeextends' => 'themeextends',
        'element'      => 'element',
        'assets'       => 'assets',
        'css'          => 'css',
        'js'           => 'js',
        'url'          => 'url',
    ],

    'tag_templates' => [
        'css'  => '<link href="%url%" rel="stylesheet" type="text/css">',
        'js'   => '<script src="%url%"></script>',
    ],

    'log' => [

        'logger_name'       => 'miravel',

        'level'             => 'error',

        // whether to log miravel messages into a separate file.
        // false              = log to the same destinations as Laravel (default);
        // true               = log to miravel.log
        // 'path/to/file.log' = log to specified file
        'separate_log'      => false,

        // THE REST OF SETTINGS TAKE EFFECT ONLY IF SEPARATE LOG IS ENABLED //

        // see https://github.com/Seldaek/monolog/blob/master/doc/01-usage.md#customizing-the-log-format
        'format'            => "[%datetime%] %message%\n",

        // date format to use in log files
        'date_format' => 'Y-m-d H:i:s',

        // 'single' or 'daily'
        'rotation'          => 'single',

        // if rotation is other than 'single', how many files to keep
        'maxfiles' => 7,

        // permissions to set on new log files
        'permissions'       => 0644,
    ],

    'theme_sources' => [
        'default' => [
            'handler'  => Miravel\ThemeSources\Marketplace::class,
            'username' => '', // for paid themes
            'key'      => '', // for paid themes
        ]
    ],

    'paths' => [
        'vendor' => 'vendor/miravel/resources/themes',
        'app'    => 'resources/views/vendor/miravel',
        'public' => 'public/miravel',
        'views'  => 'resources/views',

        'web'    => '/miravel',
    ]

];
