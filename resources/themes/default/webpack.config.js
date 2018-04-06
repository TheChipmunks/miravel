let mix              = require('laravel-mix/src/index');
let ComponentFactory = require('laravel-mix/src/components/ComponentFactory');
new ComponentFactory().installAll();

let themepath = path.normalize(path.resolve(__dirname));
Mix.paths.setRootPath(themepath);

require(Mix.paths.mix());
Mix.dispatch('init', Mix);

let WebpackConfig    = require('laravel-mix/src/builder/WebpackConfig');
module.exports       = new WebpackConfig().build();
