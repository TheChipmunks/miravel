let mix = require('../../../node_modules/laravel-mix/src/index');
let ComponentFactory = require('../../../node_modules/laravel-mix/src/components/ComponentFactory');
new ComponentFactory().installAll();

require(Mix.paths.mix());

Mix.dispatch('init', Mix);

let WebpackConfig = require('../../../node_modules/laravel-mix/src/builder/WebpackConfig');

module.exports = new WebpackConfig().build();
