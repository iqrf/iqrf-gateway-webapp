const baseUrl = process.env.VUE_APP_BASE_URL;

module.exports = {
	publicPath: baseUrl,
	runtimeCompiler: true,
	outputDir: 'www/dist',
	chainWebpack: (config) => {
		const svgRule = config.module.rule('svg');
		svgRule.uses.clear();
		svgRule
			.use('babel-loader')
			.loader('babel-loader')
			.end()
			.use('vue-svg-loader')
			.loader('vue-svg-loader');
	},
};
