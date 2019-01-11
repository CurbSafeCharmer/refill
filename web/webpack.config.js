const path = require('path');
const webpack = require('webpack');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const VueLoaderPlugin = require('vue-loader/lib/plugin');
const config = require(`./config.${process.env.NODE_ENV}.js`);

module.exports = {
  entry: {
    app: [
      './src/main.js',
      'webpack-material-design-icons'
    ]
  },
  output: {
    path: path.resolve(__dirname, './dist'),
    publicPath: config.publicPath,
    filename: '[name].js'
  },
  resolve: {
    extensions: ['.js', '.vue'],
    modules: [
      'node_modules',
      'libs',
    ],
    alias: {
      'vue$': 'vue/dist/vue.esm.js',
      'messages': path.resolve(__dirname, '../messages'),
    },
  },
  module: {
    rules: [
      {
        test: /\.css$/,
        loader: 'style-loader!css-loader',
      },
      {
        test: /\.scss$/,
        loader: 'style-loader!css-loader!sass-loader',
      },
      {
        test: /\.vue$/,
        loader: 'vue-loader',
        options: {
          loaders: {
            // Since sass-loader (weirdly) has SCSS as its default parse mode, we map
            // the "scss" and "sass" values for the lang attribute to the right configs here.
            // other preprocessors should work out of the box, no loader config like this necessary.
            'scss': 'vue-style-loader!css-loader!sass-loader',
            'sass': 'vue-style-loader!css-loader!sass-loader?indentedSyntax',
          },
          // other vue-loader options go here
        },
      },
      {
        test: /\.js$/,
        loader: 'babel-loader',
        exclude: /node_modules/,
      },
      {
        test: /\.(png|jpe?g|gif|svg|eot|woff2|woff|ttf)$/,
        loader: 'file-loader',
        options: {
          name: '[name].[ext]?[hash]',
        },
      },
      {
        test: /wdiff\.js$/,
        include: [
          path.resolve(__dirname, "libs/")
        ],
        use: 'exports-loader?WikEdDiff',
      },
      {
        test: /\.js$/,
        include: [
          path.resolve(__dirname, "node_modules/@wikimedia/jquery.i18n/src")
        ],
        use: 'imports-loader?$=jquery/src/core,jQuery=jquery/src/core',
      },
    ],
  },
  devServer: {
    historyApiFallback: {
      rewrites: [
        { from: /\/app\.js$/, to: '/app.js' },
        { from: /./, to: '/index.html' },
      ],
    },
    noInfo: true,
  },
  performance: {
    hints: false,
  },
  devtool: '#eval-source-map',
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
  module.exports.devtool = '#source-map';
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
