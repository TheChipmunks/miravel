let mix = require('laravel-mix');

mix.setPublicPath('./');

mix .sass('assets/src/style.scss', 'assets/dist')
    .scripts('assets/src/script.js', 'assets/dist');
