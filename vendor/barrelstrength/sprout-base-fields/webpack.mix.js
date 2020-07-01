let mix = require('laravel-mix');

if (mix.inProduction()) {
  // we can import this file directly from node_modules but then
  // scss variables fail to autocomplete. So, we import this file
  // from a location that is available to the plugin, but only
  // want to copy it once.
  mix.copy(
    'node_modules/craftcms-sass/src/_mixins.scss',
    'lib/craftcms-sass/_mixins.scss'
  );
}

mix
// Address
  .js([
      'src/web/assets/address/src/js/AddressBox.js',
      'src/web/assets/address/src/js/EditAddressModal.js'
    ],
    'src/web/assets/address/dist/js/addressfield.js')
  .sass(
    'src/web/assets/address/src/scss/addressfield.scss',
    'src/web/assets/address/dist/css/addressfield.css'
  )

  // Email
  .js([
    'src/web/assets/email/src/js/emailfield.js'
  ], 'src/web/assets/email/dist/js/emailfield.js')
  .sass(
    'src/web/assets/email/src/scss/emailfield.scss',
    'src/web/assets/email/dist/css/emailfield.css'
  )

  // Phone
  .js([
    'src/web/assets/phone/src/js/phonefield.js'
  ], 'src/web/assets/phone/dist/js/phonefield.js')
  .sass(
    'src/web/assets/phone/src/scss/phonefield.scss',
    'src/web/assets/phone/dist/css/phonefield.css'
  )

  // Regular Expression
  .js([
    'src/web/assets/regularexpression/src/js/regularexpressionfield.js'
  ], 'src/web/assets/regularexpression/dist/js/regularexpressionfield.js')

  // Select Other
  .js([
    'src/web/assets/selectother/src/js/SelectOtherField.js'
  ], 'src/web/assets/selectother/dist/js/selectotherfield.js')
  .sass(
    'src/web/assets/selectother/src/scss/select-other.scss',
    'src/web/assets/selectother/dist/css/select-other.css'
  )

  // URL
  .js([
    'src/web/assets/url/src/js/urlfield.js'
  ], 'src/web/assets/url/dist/js/urlfield.js')
  .sass(
    'src/web/assets/url/src/scss/urlfield.scss',
    'src/web/assets/url/dist/css/urlfield.css'
  );

// Full API
// mix.js(src, output);
// mix.react(src, output); <-- Identical to mix.js(), but registers React Babel compilation.
// mix.preact(src, output); <-- Identical to mix.js(), but registers Preact compilation.
// mix.coffee(src, output); <-- Identical to mix.js(), but registers CoffeeScript compilation.
// mix.ts(src, output); <-- TypeScript support. Requires tsconfig.json to exist in the same folder as webpack.mix.js
// mix.extract(vendorLibs);
// mix.sass(src, output);
// mix.less(src, output);
// mix.stylus(src, output);
// mix.postCss(src, output, [require('postcss-some-plugin')()]);
// mix.browserSync('my-site.test');
// mix.combine(files, destination);
// mix.babel(files, destination); <-- Identical to mix.combine(), but also includes Babel compilation.
// mix.copy(from, to);
// mix.copyDirectory(fromDir, toDir);
// mix.minify(file);
// mix.sourceMaps(); // Enable sourcemaps
// mix.version(); // Enable versioning.
// mix.disableNotifications();
// mix.setPublicPath('path/to/public');
// mix.setResourceRoot('prefix/for/resource/locators');
// mix.autoload({}); <-- Will be passed to Webpack's ProvidePlugin.
// mix.webpackConfig({}); <-- Override webpack.config.js, without editing the file directly.
// mix.babelConfig({}); <-- Merge extra Babel configuration (plugins, etc.) with Mix's default.
// mix.then(function () {}) <-- Will be triggered each time Webpack finishes building.
// mix.dump(); <-- Dump the generated webpack config object to the console.
// mix.extend(name, handler) <-- Extend Mix's API with your own components.
// mix.options({
//   extractVueStyles: false, // Extract .vue component styling to file, rather than inline.
//   globalVueStyles: file, // Variables file to be imported in every component.
//   processCssUrls: true, // Process/optimize relative stylesheet url()'s. Set to false, if you don't want them touched.
//   purifyCss: false, // Remove unused CSS selectors.
//   terser: {}, // Terser-specific options. https://github.com/webpack-contrib/terser-webpack-plugin#options
//   postCss: [] // Post-CSS options: https://github.com/postcss/postcss/blob/master/docs/plugins.md
// });
