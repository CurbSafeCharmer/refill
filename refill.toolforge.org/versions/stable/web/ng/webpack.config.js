const path = require('path');
const webpack = require('webpack');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const { VueLoaderPlugin } = require('vue-loader');
const config = require(`./config.${process.env.NODE_ENV}.js`);

module.exports = {
  entry: {
    app: [
      './src/main.js'
    ]
  },
  output: {
    path: path.resolve(__dirname, './dist'),
    publicPath: config.publicPath,
    filename: '[name].[hash].js',
    assetModuleFilename: '[name].[hash][ext]',
  },
  resolve: {
    extensions: ['.js', '.vue'],
    modules: [
      'node_modules',
      'libs',
    ],
    alias: {
      'vue$': 'vue/dist/vue.esm-bundler.js',
      'messages': path.resolve(__dirname, '../../../../../messages'),
    },
  },
  module: {
    rules: [
      {
        test: /\.css$/,
        use: [ 'style-loader', 'css-loader' ],
      },
      {
        test: /\.scss$/,
        use: [
          'style-loader',
          'css-loader',
          {
            loader: 'sass-loader',
            options: {
              implementation: require('sass'),
              sassOptions: {
                quietDeps: true
              }
            }
          }
        ],
      },
      {
        test: /\.vue$/,
        loader: 'vue-loader',
        options: {
          loaders: {
            // Since sass-loader (weirdly) has SCSS as its default parse mode, we map
            // the "scss" and "sass" values for the lang attribute to the right configs here.
            // other preprocessors should work out of the box, no loader config like this necessary.
            scss: [ 'vue-style-loader', 'css-loader', {
              loader: 'sass-loader',
              options: {
                implementation: require('sass'),
                sassOptions: { quietDeps: true }
              }
            } ],
            sass: [ 'vue-style-loader', 'css-loader', {
              loader: 'sass-loader',
              options: {
                implementation: require('sass'),
                sassOptions: { indentedSyntax: true, quietDeps: true }
              }
            } ],
          },
          // other vue-loader options go here
        },
      },
      {
        test: /\.(png|jpe?g|gif|svg|eot|woff2|woff|ttf)$/,
        type: 'asset/resource'
      },
    ],
  },
  devServer: {
    port: 8087,
    historyApiFallback: {
      rewrites: [
        { from: /\/app\.js$/, to: '/app.js' },
        { from: /./, to: '/index.html' },
      ],
    },
  },
  // Suppress deprecation warnings emitted by Dart Sass legacy JS API
  ignoreWarnings: [
    warning => {
      try {
        return typeof warning.message === 'string' && /The legacy JS API is deprecated/.test(warning.message);
      } catch (e) {
        return false;
      }
    }
  ],
  performance: {
    hints: false,
  },
  devtool: 'eval-source-map',
  plugins: [
    new HtmlWebpackPlugin({
      template: 'template.html',
    }),
    new VueLoaderPlugin(),
    new webpack.DefinePlugin({
      'staticConfig': JSON.stringify(config),
    }),
  ],
  optimization: {},
}

if (process.env.NODE_ENV === 'production') {
  module.exports.devtool = 'source-map';
  // http://vue-loader.vuejs.org/en/workflow/production.html
  module.exports.plugins = (module.exports.plugins || []).concat([
    new webpack.DefinePlugin({
      'process.env': {
        NODE_ENV: '"production"',
      },
    }),
    new webpack.LoaderOptionsPlugin({
      minimize: true,
    }),
  ]);
}

module.exports.mode = process.env.NODE_ENV;
