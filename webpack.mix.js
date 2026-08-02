const mix = require('laravel-mix');
mix.setPublicPath('public/assets').setResourceRoot('/assets/');
mix.js('resources/js/app.js', 'js')
   .postCss('resources/css/app.css', 'css', [
       require('postcss-import'),
       require('tailwindcss'),
       require('autoprefixer'),
   ])
   .version()
   .options({ terser: { extractComments: false } });
mix.copy('node_modules/bootstrap/dist/css/bootstrap.min.css', 'public/assets/css/vendor/bootstrap.min.css');
mix.copy('node_modules/bootstrap/dist/js/bootstrap.bundle.min.js', 'public/assets/js/vendor/bootstrap.bundle.min.js');
mix.copy('node_modules/chart.js/dist/chart.umd.min.js', 'public/assets/js/vendor/chart.min.js');
mix.copy('node_modules/@fortawesome/fontawesome-free/css/all.min.css', 'public/assets/css/vendor/fontawesome.min.css');
mix.copy('node_modules/@fortawesome/fontawesome-free/webfonts', 'public/assets/webfonts');
