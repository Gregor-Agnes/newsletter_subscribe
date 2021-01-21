const merge = require('webpack-merge');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const ImageminPlugin = require("imagemin-webpack");
const common = require('./webpack.common.js');
const CopyPlugin = require('copy-webpack-plugin');

module.exports = merge(common, {
  mode: 'development',
  devtool: 'inline-source-map',
  output: {
    publicPath: 'https://localhost:8080/',
  },
  devServer: {
    contentBase: 'Resources/Dev',
    watchContentBase: true,
    hot: true,
    https: true,
    disableHostCheck: true,
    publicPath: '/',
    headers: {
      'Access-Control-Allow-Origin': '*'
    },
  },
  module: {
    rules: [
      {
        test: /\.css$/,
        use: [
          {
            loader: 'cache-loader',
          },
          {
            loader: "style-loader"
          }, {
            loader: "css-loader",
            options: {
              sourceMap: true,
            }
          }],
      },
      {
        test: /\.s[ac]ss$/i,
        use: [
          {
            loader: 'cache-loader',
          },
          {
            loader: "style-loader"
          }, {
            loader: "css-loader",
            options: {
              sourceMap: true,
            }
          }, {
            loader: "sass-loader",
            options: {
              sourceMap: true,
            }
          }],
      },
      {
        test: /\.(png|svg|jpg|gif)$/,
        use: [
          {
            loader: 'cache-loader',
          },
          {
            loader: 'url-loader',
          }
        ]
      },
      {
        test: /\.(woff|woff2|eot|ttf|otf)$/,
        use: [
          {
            loader: 'cache-loader',
          },
          {
            loader: 'url-loader',
          }
        ],
      },
    ],
  },
  plugins: [
    new CopyPlugin([
      {
        from: 'Gfx/**/*.*',
        to: '../Public',
        toType: 'dir',
        context: './Resources/Private/',
      },
    ]),
    new ImageminPlugin({
      disable: true,
      bail: false, // Ignore errors on corrupted images
      cache: true,
      imageminOptions: {
        plugins: [
          ["gifsicle", {interlaced: true}],
          ["jpegtran", {progressive: true}],
          ["optipng", {optimizationLevel: 5}],
          [
            "svgo",
            {
              plugins: [
                {
                  removeViewBox: false
                }
              ]
            }
          ]
        ]
      }
    }),
    new CopyPlugin([
      {
        from: 'Icons/**/*.*',
        to: '../Public',
        toType: 'dir',
        context: './Resources/Private/',
      },
    ]),
    new CopyPlugin([
      {
        from: 'RTE/**/*.*',
        to: '../Public',
        toType: 'dir',
        context: './Resources/Private/',
      },
    ]),
  ],
  optimization: {
    minimize: false,
  }

});