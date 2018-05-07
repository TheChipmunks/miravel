let moduleName = 'laravel-mix'
global.modulePath = require.resolve('laravel-mix')
modulePath = modulePath.substring(0, modulePath.indexOf(moduleName) + moduleName.length + 1)

let modulePackage = require(modulePath + 'package.json')
let mix = require('laravel-mix');
let versionCompare = require('./versionCompare');
let argv = require('yargs').argv;

global.ThemePath = argv.env.themepath;
global.MixVersion = modulePackage.version;
global.File = require('./File');

console.log("Theme: " + ThemePath);
console.log("Public: " + ThemePath + 'public');

Config.publicPath = ThemePath + 'public';
mix.options({
    clearConsole: false
});

if(1 === versionCompare(String(MixVersion), '1.9.9')){
	let ComponentFactory = require(modulePath + 'src/components/ComponentFactory');
	new ComponentFactory().installAll();
}

require(Mix.paths.mix());

Mix.dispatch('init', Mix);

let WebpackConfig = require(modulePath + 'src/builder/WebpackConfig');

module.exports = new WebpackConfig().build();
