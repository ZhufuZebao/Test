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
// 「.evn」の変数よりフォントの参照パスを修正：publicPath: process.env.APP_DIR,
mix.webpackConfig({
  module: {
    rules: [
      {
        test: /(\.(woff2?|ttf|eot|otf)$|font.*\.svg$)/,
        loader: 'file-loader',
        options: {
          publicPath: process.env.APP_DIR,
          name: path => {
            if (!/node_modules|bower_components/.test(path)) {
              return mix.config.fileLoaderDirs.fonts + '/[name].[ext]?[hash]';
            }
            return (
                mix.config.fileLoaderDirs.fonts
                + '/vendor/'
                + path.replace(/\\/g, '/').replace(/((.*(node_modules|bower_components))|fonts|font|assets)\//g, '')
                + '?[hash]'
            );
          },
        },
      },
    ]
  }
});
mix.config.webpackConfig.output = {
  chunkFilename: `js/[name].js?id=[chunkhash]`,
  publicPath: `${process.env.APP_DIR}`,
};
mix.config.webpackConfig.externals = {
  'element-ui': 'Element',
  'vue': 'Vue',
  'vue-router': 'VueRouter',
  'jquery': '$',
  'lodash': '_',
  'axios': 'axios',
  'vue-resource': 'VueResource',
};

mix.version();

mix.js('resources/assets/js/app.js', 'public/js')
   .sass('resources/assets/sass/app.scss', 'public/css');
mix.js('resources/assets/js/admin.js', 'public/js')
   .sass('resources/assets/sass/admin.scss', 'public/css');