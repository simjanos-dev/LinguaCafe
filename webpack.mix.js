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

mix.js('resources/js/app.js', 'public/js')
    .vue()
    .sass('resources/sass/reader.scss', 'public/css')
    .sass('resources/sass/ebook_reader_mode.scss', 'public/css')
    .sass('resources/sass/kanji_print.scss', 'public/css')
    .sass('resources/sass/app.scss', 'public/css')
    .browserSync({
        proxy: 'langdev.com',
        host: 'langdev.com',
        open: 'external'
    });
    