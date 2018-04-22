let mix = require('laravel-mix');

let themepath = path.resolve(__dirname, './');
Mix.paths.setRootPath(themepath)
mix.setPublicPath(themepath);
mix.options({
    clearConsole: false
});

mix .sass('assets/src/style.scss', 'assets/dist')
    .scripts('assets/src/script.js', 'assets/dist/script.js');
