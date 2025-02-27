const mix = require('laravel-mix');

mix.js('assets/js/app.js', 'build/js')
   .vue()
   .sass('assets/scss/style.scss', 'build/css')
   .setPublicPath('assets');