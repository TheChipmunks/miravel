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
