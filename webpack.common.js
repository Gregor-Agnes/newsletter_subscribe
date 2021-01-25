const path = require('path');

module.exports = {
  target: 'web',
  mode: 'development',
  entry: {
    NewsletterSubscribe: ['./Resources/Private/JavaScript/NewsletterSubscribe.js']
  },
  output: {
    filename: 'JavaScript/[name].js',
    path: path.resolve(__dirname + '/Resources/Public')
  },
  optimization: {
    splitChunks: {
      cacheGroups: {
        vendor: {
          test: /[\\/]node_modules[\\/]/,
          name: 'JavaScriptLibs',
          chunks: 'all',
        },
        styles: {
          name: 'styles',
          test: /\.css$/,
          chunks: 'all',
        },
      },
    },
  },
};
