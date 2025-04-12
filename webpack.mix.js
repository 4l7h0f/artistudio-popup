const mix = require('laravel-mix');


// Frontend assets
mix.js('resources/js/app.js', 'public/js')
   .vue()
   .sass('resources/sass/app.scss', 'css')
   .setPublicPath('public');

// Admin assets
mix.js('resources/js/admin/admin.js', 'public/js/admin.js')
   .vue()
   .sass('resources/sass/admin.scss', 'public/css/admin.css')
   .setPublicPath('public');
