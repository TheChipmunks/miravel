let mix = require('laravel-mix');
let fs = require('fs');
let argv = require('yargs').argv;

global.ThemePath =  argv.env.themepath;
global.RootPath =  Mix.paths.root();
global.LaravelMixPath =  path.resolve(RootPath, 'node_modules/laravel-mix/');
global.File = require('./File');

console.log("Theme: " + ThemePath);
console.log("Public: " + ThemePath + 'public');

Config.publicPath = ThemePath + 'public';
mix.options({
    clearConsole: false
});

console.log('LaravelMixPath: ' + LaravelMixPath);
console.log('global.LaravelMixPath: ' + global.LaravelMixPath);

fs.exists(global.LaravelMixPath + '/src/components/ComponentFactory.js', function(exists) {
	console.log(exists);
    if (exists) {
    		let ComponentFactory = require(LaravelMixPath + '/src/components/ComponentFactory');
    		new ComponentFactory().installAll();
    }
});

require(Mix.paths.mix());

Mix.dispatch('init', Mix);

let WebpackConfig = require(LaravelMixPath + '/src/builder/WebpackConfig');

module.exports = new WebpackConfig().build();
