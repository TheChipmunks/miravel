let mix = require('../../../node_modules/laravel-mix/src/index');
let ComponentFactory = require('../../../node_modules/laravel-mix/src/components/ComponentFactory');
new ComponentFactory().installAll();

let themepath = path.resolve(__dirname, './');
Mix.paths.setRootPath(themepath)
mix.setPublicPath(themepath);
mix.options({
    clearConsole: false
});

require(Mix.paths.mix());

Mix.dispatch('init', Mix);

let WebpackConfig = require('../../../node_modules/laravel-mix/src/builder/WebpackConfig');

module.exports = new WebpackConfig().build();
