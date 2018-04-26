/*
 * node node_modules/cross-env/dist/bin/cross-env.js NODE_ENV=development node_modules/webpack/bin/webpack.js --progress --hide-modules --config=vendor/miravel/miravel/mix/webpack.config.js  --env.themepath=storage/miravel/test-parent/ --env.mixfile=storage/miravel/test-parent/webpack.mix.js
 */
let mix = require('laravel-mix');
let argv = require('yargs').argv;

global.File = require('./File');
global.ThemePath =  argv.env.themepath;
global.RootPath =  Mix.paths.root();

console.log("Theme: " + ThemePath);
console.log("Public: " + ThemePath + 'public');

Config.publicPath = ThemePath + 'public';
mix.options({
    clearConsole: false
});

require(Mix.paths.mix());

Mix.dispatch('init', Mix);

let WebpackConfig = require('../../../../node_modules/laravel-mix/src/builder/WebpackConfig');

module.exports = new WebpackConfig().build();
