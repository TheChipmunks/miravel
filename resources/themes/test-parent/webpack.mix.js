let mix = require('laravel-mix');

mix .sass('assets/src/style.scss', 'public/css/test.mix.css')
    .js(['assets/src/script.js'], 'public/js/test.mix.js');
