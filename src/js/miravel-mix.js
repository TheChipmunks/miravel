let mix = require('laravel-mix');

class Miravel {

    webpackConfig(config) {
        console.log('I am alive');

        // OUR LOGIC HERE
        // resolve all paths and update webpack config...
    }

}

mix.extend('miravel', new Miravel())
