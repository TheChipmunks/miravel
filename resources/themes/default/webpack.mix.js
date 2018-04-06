let mix = require('laravel-mix');

require('../../../src/js/miravel-mix');

let themepath = path.normalize(path.resolve(__dirname));
mix.setResourceRoot(path.normalize(themepath));
mix.setPublicPath(path.normalize(themepath));

mix
    .scripts([
        'assets/vendor/jquery/jquery-3.2.1.slim.js',
        'assets/vendor/popper/popper.js',
        'assets/vendor/bootstrap4/bootstrap.js',
        'assets/vendor/modernizr/modernizr-3.5.0.min.js'
    ],
        'assets/dist/scripts.js')



    .sass(
        'assets/src/styles.scss',

        'assets/dist/styles.css'
    )

    .miravel();
