let mix              = require('laravel-mix/src/index');
let ComponentFactory = require('laravel-mix/src/components/ComponentFactory');
new ComponentFactory().installAll();
let WebpackConfig    = require('laravel-mix/src/builder/WebpackConfig');

mix.setRootPath(path.resolve(__dirname));

require(Mix.paths.mix());

module.exports = new WebpackConfig().build();
