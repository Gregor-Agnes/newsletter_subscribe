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
<thead>
<tr>
<th>Parameter</th>
<th>Description</th>
<th>Type, Validation</th>
<th>Default</th>
</tr>
</thead>
  <tr>
    <th align="left">dataProtectionPage</th>
    <td align="left">Uid of page with information about the data protection policy</td>
    <td>integer</td>
    <td>1</td>
  </tr>
  <tr>
    <th align="left">adminEmail</th>
    <td align="left">Email address in confirmation mails</td>
    <td>string, email, mandatory</td>
    <td>admin.name@domain.tld</td>
  </tr>
  <tr>
    <th align="left">adminName</th>
    <td align="left">Name in confirmation mails</td>
    <td>string</td>
    <td>Your admin Name</td>
  </tr>
  <tr>
    <th align="left">newsLetterName</th>
    <td align="left">Title of the newsletter / subscription list</td>
    <td>string</td>
    <td>Newsletter</td>
  </tr>
  <tr>
    <th align="left">showFields</th>
    <td align="left">Fields to show in subscription form (gender,firstName,lastName).<br>email and dataProtection are always shown.</td>
    <td>string</td>
    <td>null</td>
  </tr>
  <tr>
    <th align="left">overrideFlexformSettingsIfEmpty</th>
    <td align="left">Fields, which sould be overridden from TypoScript if left blank in the flexform (like in tx_news, thx to Georg Ringer!).</td>
    <td>string</td>
    <td>dataProtectionPage, adminName, showFields, newsletterName</td>
  </tr>
</table>

## Site config (for nicer link in subscriber mails)

```yaml
routeEnhancers:
  UnSubscribe:
    type: Extbase
    extension: NewsletterSubscribe
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
    extension: NewsletterSubscribe
    plugin: Subscribe
    routes:
      -
        routePath: '/confirm/{confirm}/{uid}'
        _controller: 'Subscribe::doConfirm'
        _arguments:
          confirm: subscriptionHash
          uid: uid
      -
        routePath: '/undosubscribe/{unsubscribe}/{uid}'
        _controller: 'Subscribe::undosubscribe'
        _arguments:
          unsubscribe: subscriptionHash
          uid: uid
```
## Unsubscribe link in direkt mail
1. First add the field subscription_hash to the fields of direct mail in the extension configuration of direct mail: 
![direct mail configuration](https://github.com/Gregor-Agnes/newsletter_subscribe/raw/master/Resources/Public/Gfx/ExtManDirectMail1.png)
2. Add the link in your mail template:\
`<a href="http://www.domain.tld/page/undosubscribe/###USER_subscription_hash###/###USER_uid###">unsubscribe</a>`
where this `undosubscribe/###USER_subscription_hash###/###USER_uid###"` is the important part.<br>Note: The subscribe plugin must be inserted on the page "page" in that url.


***

# Credits
Extension icon from: [2020 Icons8 LLC.](https://icons8.de/)

***

# To do
- write a scheduler task to remove unconfirmed addresses (after some time)
- creating ajax submit
