#subscribe#

* Subcribe for Newsletters for TYPO3 >= 9.5
* Depends on tt_address

##What does it do?##

* Provides a plugin to double optin subscribe
* Provides a plugin to double optin unsubscribe
* Provides field in tt_address to generate unsubscribe link in direct_mail mailings

##site config (for nicer link in subscriber mails)##

```yaml

	  UnSubscribe:
	    type: Extbase
	    extension: Subscribe
	    plugin: Unsubscribe
	    routes:
	      -
	        routePath: '/unsubscribe/{unsubscribe}/{uid}'
	        _controller: 'Subscribe::unsubscribe'
	        _arguments:
	          unsubscribe: subscriptionHash
	          uid: uid
	  Subscribe:
	    type: Extbase
	    extension: Subscribe
	    plugin: Subscribe
	    routes:
	      -
	        routePath: '/confirm/{confirm}/{uid}'
	        _controller: 'Subscribe::doConfirm'
	        _arguments:
	          confirm: subscriptionHash
	          uid: uid
	      -
	        routePath: '/unsubscribe/{unsubscribe}/{uid}'
	        _controller: 'Subscribe::unsubscribe'
	        _arguments:
	          unsubscribe: subscriptionHash
	          uid: uid
```