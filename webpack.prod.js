const merge = require('webpack-merge');
const common = require('./webpack.common.js');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const autoprefixer = require('autoprefixer')
const {CleanWebpackPlugin} = require('clean-webpack-plugin');
const ImageminPlugin = require('imagemin-webpack-plugin').default
const CopyPlugin = require('copy-webpack-plugin');
const glob = require('glob')

module.exports = merge(common, {
  mode: 'production',
  //devtool: 'source-map',
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: [/core-js/, /regenerator-runtime/],
        use: {
          loader: 'babel-loader',
          options: {
            presets: [
              '@babel/preset-env',
            ]
          }
        }
      },
      {
        test: /\.css$/i,
        use: [
          {
            loader: MiniCssExtractPlugin.loader,
            options: {
              publicPath: 'Css/',
              name: '[name]/app.[ext][query]',
            },
          },
          'css-loader'
        ]
      },
      {
        test: /\.s[ac]ss$/i,
        use: [
          {
            loader: MiniCssExtractPlugin.loader,
            options: {
              publicPath: 'Css/',
              name: '[name]/app.[ext][query]',
            },
          },
          {
            loader: 'css-loader',
          },
          {
            loader: 'postcss-loader',
            options: {
              plugins: () => [autoprefixer()]
            }
          },
          {
            loader: 'sass-loader',
          }
        ]
      },
      {
        test: /\.(woff|woff2|eot|ttf|otf)$/,
        use: [
          {
            loader: 'file-loader',
            options: {
              name: 'Fonts/[name].[ext][query]',
              publicPath: '../',
            },
          }
        ],
      },
      {
        test: /\.(png|svg|jpe?g|gif)$/i,
        use: [
          {
            loader: 'file-loader',
            options: {
              publicPath: '../Gfx',
              name: '[name].[ext]',
              emitFile: false
            }
          },
        ],
      },
    ],
  },
  plugins: [
    new CleanWebpackPlugin(),
    new ImageminPlugin({
      externalImages: {
        context: './Resources/',
        sources: glob.sync('./Resources/Private/Gfx/**/*.{jpg,gif,png,svg}')
          .concat(glob.sync('./node_modules/leaflet/dist/images/**/*.{jpg,gif,png,svg}'))
          .concat(glob.sync('./node_modules/leaflet.fullscreen/**/*.{jpg,gif,png,svg}')),
        destination: './Resources/Public/Gfx',
        fileName: '[name].[ext]' // (filePath) => filePath.replace('jpg', 'webp') is also possible
      },
      imageminOptions: {
        // Before using imagemin plugins make sure you have added them in `package.json` (`devDependencies`) and installed them

        // Lossless optimization with custom option
        // Feel free to experiment with options for better result for you
        plugins: [
          ["gifsicle", {interlaced: true}],
          ["jpegtran"],
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
    new MiniCssExtractPlugin({
      // Options similar to the same options in webpackOptions.output
      // both options are optional
      filename: './Css/[name].css',
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
    minimize: true,
  }
});