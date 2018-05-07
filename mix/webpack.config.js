let mix = require('laravel-mix');
let versionCompare = require('./versionCompare');
let fs = require('fs');
let argv = require('yargs').argv;

global.ThemePath =  argv.env.themepath;
global.MixVersion =  argv.env.mix_version;
global.RootPath =  Mix.paths.root();
global.LaravelMixPath =  path.resolve(RootPath, 'node_modules/laravel-mix/');
global.File = require('./File');

console.log("Theme: " + ThemePath);
console.log("Public: " + ThemePath + 'public');

Config.publicPath = ThemePath + 'public';
mix.options({
    clearConsole: false
});

if(1 === versionCompare(String(MixVersion), '1.9.9')){
	var ComponentFactory = require(LaravelMixPath + '/src/components/ComponentFactory');
	new ComponentFactory().installAll();
}

require(Mix.paths.mix());

Mix.dispatch('init', Mix);

let WebpackConfig = require(LaravelMixPath + '/src/builder/WebpackConfig');

module.exports = new WebpackConfig().build();
