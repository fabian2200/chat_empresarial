const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .vue({ version: 3 })
   .sass('resources/sass/app.scss', 'public/css')
   .setResourceRoot('/chat-empresarial/public/')
   .setPublicPath('public')
   .version();

if (mix.inProduction()) {
    mix.version();
} 