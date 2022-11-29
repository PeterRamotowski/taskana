const Encore = require('@symfony/webpack-encore');
const webpack = require('webpack');

const { BundleAnalyzerPlugin } = require('webpack-bundle-analyzer');

if (!Encore.isRuntimeEnvironmentConfigured()) {
	Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore.setOutputPath('./public/assets')
	.setPublicPath('/assets')

	.addEntry('global', './assets/global.js')
	.addEntry('login', './assets/login.js')
	.addEntry('vue', './assets/vue/index.js')

	.enableStimulusBridge('./assets/controllers.json')

	.splitEntryChunks()
	.enableSingleRuntimeChunk()
	.cleanupOutputBeforeBuild()
	.enableBuildNotifications()
	.enableSourceMaps(!Encore.isProduction())
	.enableVersioning(Encore.isProduction())

	.configureBabelPresetEnv((config) => {
		config.useBuiltIns = 'usage';
		config.corejs = '3.23';
	})
	.configureBabel((babelConfig) => {
		babelConfig.plugins = ['@babel/plugin-transform-runtime'];
	})

	.enableEslintPlugin()

	.enableSassLoader()
	.enableVueLoader(() => {}, { runtimeCompilerBuild: false, version: 3 });

if (!Encore.isProduction()) {
	Encore.addPlugin(
		new BundleAnalyzerPlugin({
			analyzerMode: 'static',
			reportFilename: 'report.html',
			openAnalyzer: false,
		}),
	);
}

Encore.addPlugin(
	new webpack.DefinePlugin({
		__VUE_OPTIONS_API__: false,
		__VUE_PROD_DEVTOOLS__: true,
	}),
);

module.exports = Encore.getWebpackConfig();
