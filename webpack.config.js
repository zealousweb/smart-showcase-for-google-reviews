const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const { WebpackManifestPlugin } = require('webpack-manifest-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

module.exports = {
  mode: 'development', // Change to 'production' for a production build
  entry: {
    main: './assets/src/js/index.js', // Main JavaScript entry
    backend: './assets/src/js/backend/index.js',
    styles: {
      import: './assets/src/scss/style.scss', // SCSS entry
      dependOn: 'main', // Links SCSS to main.js so it doesn't generate styles.js
    },
  },
  output: {
    path: path.resolve(__dirname, 'assets/dist'),
    filename: '[name].js', // Generates 'main.js' but NOT 'styles.js'
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env'],
          },
        },
      },
      {
        test: /\.scss$/,
        use: [
          MiniCssExtractPlugin.loader,
          'css-loader',
          'sass-loader',
        ],
      },
    ],
  },
  plugins: [
    new CleanWebpackPlugin(),
    new MiniCssExtractPlugin({
      filename: 'styles.css', // Extracts SCSS into this CSS file
    }),
    new WebpackManifestPlugin({
      publicPath: 'dist/',
      basePath: 'dist/'
    }),
  ],
  devtool: 'source-map',
};
