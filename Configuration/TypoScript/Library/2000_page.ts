page {
	includeCSS {
		NewsletterSubscribe = EXT:newsletter_subscribe/Resources/Public/Css/NewsletterSubscribe.css
		#NewsletterSubscribeLibs = EXT:newsletter_subscribe/Resources/Public/Css/JavaScriptLibs.css
	}

	includeJSFooterlibs {
		#NewsletterSubscribeLibs = EXT:newsletter_subscribe/Resources/Public/JavaScript/JavaScriptLibs.js
	}

	includeJSFooter {
		NewsletterSubscribe = EXT:newsletter_subscribe/Resources/Public/JavaScript/NewsletterSubscribe.js
	}
}

[traverse(request.getQueryParams(), 'dev-nl') > 0]
	page.meta.robots = noindex
	config {
		linkVars := addToList(dev-nl(1))
		no_cache = 1
		compressCss = 0
		compressJs = 0
	}

	page.includeCSS {
		NewsletterSubscribe >
		NewsletterSubscribeLibs >
	}

	page {
		includeJSFooterlibs {
			NewsletterSubscribeLibs = https://localhost:8080/JavaScript/JavaScriptLibs.js
			NewsletterSubscribeLibs.external = 1
		}
		includeJSFooter {
			NewsletterSubscribe = https://localhost:8080/JavaScript/NewsletterSubscribe.js
			NewsletterSubscribew.external = 1
		}
	}
[global]