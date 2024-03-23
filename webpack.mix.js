const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/vue3/app.js', 'public/vue3')
    .vue()
    .css('resources/vue3/app.css', 'public/vue3')
    .browserSync({
        proxy: 'lingua.cafe',
        host: 'lingua.cafe',
        open: 'external'
    })
    .version();
    