# subscribe

* Subcribe for Newsletters for TYPO3 >= 9.5
* Depends on tt_address

## What does it do?

* Provides a plugin to double optin subscribe
* Provides a plugin to double optin unsubscribe
* Provides field in tt_address to generate unsubscribe link in direct_mail mailings

## Install
* Install via extension manager or
* Install via composer
* Include static template

## Configuration
<table>
  <tr>
    <th align="left">dataProtectionPage</th>
    <td align="left">Uid of page with information about the data protection policy</td>
  </tr>
  <tr>
    <th align="left">adminEmail</th>
    <td align="left">Email address in confirmation mails</td>
  </tr>
  <tr>
    <th align="left">adminName</th>
    <td align="left">Name in confirmation mails</td>
  </tr>
</table>

## Site config (for nicer link in subscriber mails)

```yaml
routeEnhancers:
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
# Unsubscribe link in direkt mail
1. First add the field subscription_hash to the fields of direct mail in the extension configuration of direct mail: 
![direct mail configuration](https://github.com/Gregor-Agnes/subscribe/raw/master/Resources/Public/Gfx/ExtManDirectMail1.png)
2. Add the link in your mail template:\
`<a href="http://www.domain.tld/page/unsubscribe/###USER_subscription_hash###/###USER_uid###">unsubscribe</a>`
where this `unsubscribe/###USER_subscription_hash###/###USER_uid###"` is the important part.