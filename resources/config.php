<?php

return [
    'html' => [
        'charset' => 'utf-8',
    ],

    'assets' => [
        'storage'                      => [
            'engine' => 'disk',
            'path'   => 'public/miravel',
        ],
        'enable_cache'                 => true,
        'enable_route'                 => true,
        'contatenation'                => true,
        'enable_content_version_check' => true,
        'enable_cache_busting'         => true,
    ],

    'template_file_extensions' => ['php', 'blade.php', 'phtml'],

    'template_file_name'       => 'view',

    'blade_directive_map' => [

        // the right part is variable, change to your liking

        'themeInclude' => 'themeinclude',
        'themeExtends' => 'themeextends',
        'element'      => 'element',
        'assets'       => 'assets',
        'styles'       => 'styles',
        'scripts'      => 'scripts',
        'style'        => 'css',
        'script'       => 'js',
        'img'          => 'img',
    ],

    'link_patterns' => [
        'css' => '<link href="%url%" rel="stylesheet" type="text/css" />',
        'js'  => '<script src="%url%"></script>',
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

];
