const Encore = require('@symfony/webpack-encore');
const fs = require('fs');

let certPath = 'C:\\Users\\' + process.env.username + '\\AppData\\Local\\mkcert\\'
if (typeof (process.env.USER) != "undefined") {
	certPath = '/Users/' + process.env.USER + '/Library/Application Support/mkcert/'
}

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
	Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore

	// Copy some static images to your -> https://symfony.com/doc/current/frontend/encore/copy-files.html
	.copyFiles({
		from: 'Resources/Private/Gfx',
		includeSubdirectories: false,
		to: 'images/[name].[ext]',
		pattern: /\.(png|jpg|jpeg|svg)$/
	})
	// directory where compiled assets will be stored
  .setOutputPath("Resources/Public")
  .setPublicPath("/_assets/837857e6dbf3ef80d8c8323f367dabde")
	// only needed for CDN's or sub-directory deploy
	.setManifestKeyPrefix('./Resources/Public')

	.configureDevServerOptions(options => {
		options.allowedHosts = 'all';
		options.https = {
			key: fs.readFileSync(certPath + 'localhost-key.pem'),
			cert: fs.readFileSync(certPath + 'localhost.pem')

			//C:\Users\ga\AppData\Local\mkcert
		}
		options.client = {
			webSocketURL: "wss://localhost:8080/ws"
		}
	})


	/*
	 * ENTRY CONFIG
	 *
	 * Add 1 entry for each "page" of your app
	 * (including one that's included on every page - e.g. "app")
	 *
	 * Each entry will result in one JavaScript file (e.g. app.js)
	 * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
	 */
	.addEntry('NewsletterSubscribe', './Resources/Private/JavaScript/NewsletterSubscribe.js')
	//.addEntry('page1', './assets/page1.js')
	//.addEntry('page2', './assets/page2.js')

	// When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
	.splitEntryChunks()

	// will require an extra script tag for runtime.js
	// but, you probably want this, unless you're building a single-page app
	.enableSingleRuntimeChunk()

	/*
	 * FEATURE CONFIG
	 *
	 * Enable & configure other features below. For a full
	 * list of features, see:
	 * https://symfony.com/doc/current/frontend.html#adding-more-features
	 */
	.cleanupOutputBeforeBuild(
		['**/*', '!favicons.html', '!assets/**']
	)
	.enableBuildNotifications()
	.enableSourceMaps(!Encore.isProduction())
	// enables hashed filenames (e.g. app.abc123.css)
	//.enableVersioning(Encore.isProduction())

	// enables @babel/preset-env polyfills
	.configureBabelPresetEnv((config) => {
		config.useBuiltIns = 'entry';
		config.corejs = 3;
	})

	// enables Sass/SCSS suppor.enableSassLoader()
	//.enableSassLoader()
	// uncomment if you use TypeScript
	//.enableTypeScriptLoader()
	// uncomment if you use the postcss -> https://symfony.com/doc/current/frontend/encore/postcss.html
	.enablePostCssLoader()

// uncomment to get integrity="..." attributes on your script & link tags
// requires WebpackEncoreBundle 1.4 or higher
//.enableIntegrityHashes(Encore.isProduction())

// uncomment if you're having problems with a jQuery plugin
//.autoProvidejQuery()

// uncomment if you use API Platform Admin (composer require api-admin)
//.enableReactPreset()
//.addEntry('admin', './assets/admin.js')

const newsletter_subscribe = Encore.getWebpackConfig();
newsletter_subscribe.name = 'newsletter_subscribe';


module.exports = [newsletter_subscribe]
