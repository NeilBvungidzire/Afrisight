const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js').version()
mix.sass('resources/sass/app.scss', 'public/css').version();

mix.js('resources/js/admin.js', 'public/js').version();
mix.sass('resources/sass/admin.scss', 'public/css').version();

mix.js('resources/js/utils/utils.js', 'public/js').version();

mix.js('resources/js/service-worker.js', 'public');
