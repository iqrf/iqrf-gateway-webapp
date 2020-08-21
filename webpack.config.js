const webpack = require('webpack');
const path = require('path');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const VueLoaderPlugin = require('vue-loader/lib/plugin');

module.exports = {
	entry: {
		app: './js/app.js',
		config: './js/config.js',
		error500: './js/error500.js',
		iqrfNet: './js/iqrfNet.js',
	},
	output: {
		filename: '[name].bundle.js',
		path: path.resolve(__dirname, 'www/dist')
	},
	module: {
		rules: [
			{
				test: /\.vue$/,
				loader: 'vue-loader'
			},
			{
				enforce: 'pre',
				test: /\.js$/,
				exclude: /node_modules/,
				loader: 'eslint-loader',
			},
			{
				test: /\.js$/,
				exclude: /node_modules/,
				loader: 'babel-loader',
				options: {
					cacheDirectory: true,
					'presets': [
						[
							'@babel/preset-env', {
								'targets': '> 0.25%, not dead'
							}
						]
					]
				},
			},
			{
				test: /\.css$/,
				use: [
					'vue-style-loader',
					MiniCssExtractPlugin.loader,
					'css-loader',
				],
			},
			{
				test: /\.(ttf|eot|svg|woff(|2))(\?[\s\S]+)?$/,
				use: 'file-loader?name=fonts/[name].[ext]',
			},
			{
				test: /\.(jpe?g|png|gif|svg)$/i,
				use: [
					'file-loader?name=images/[name].[ext]',
				]
			},
		]
	},
	plugins: [
		new webpack.ProvidePlugin({
			$: 'jquery',
			jQuery: 'jquery',
			'window.jQuery': 'jquery',
			Nette: 'nette-forms',
			'window.Nette': 'nette-forms',
		}),
		new MiniCssExtractPlugin({
			filename: '[name].bundle.css',
		}),
		new CopyWebpackPlugin({
			patterns: [
				{
					from: './node_modules/live-form-validation/live-form-validation.js',
					to: './',
				},
			]
		}),
		new VueLoaderPlugin(),
	],
	optimization: {
		minimize: true,
		minimizer: [
			new TerserPlugin(),
			new OptimizeCSSAssetsPlugin({})
		],
	},
	resolve: {
		alias: {
			'vue$': 'vue/dist/vue.esm.js'
		},
		extensions: ['*', '.js', '.vue', '.json']
	}
};

if (process.env.NODE_ENV === 'production') {
	module.exports.devtool = '#source-map'
	// http://vue-loader.vuejs.org/en/workflow/production.html
	module.exports.plugins = (module.exports.plugins || []).concat([
		new webpack.DefinePlugin({
			'process.env': {
				NODE_ENV: '"production"'
			}
		}),
		new webpack.optimize.UglifyJsPlugin({
			sourceMap: true,
			compress: {
				warnings: false
			}
		}),
		new webpack.LoaderOptionsPlugin({
			minimize: true
		})
	])
}
