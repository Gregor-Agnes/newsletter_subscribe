<?php
defined('TYPO3_MODE') || die('Access denied.');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Zwo3.NewsletterSubscribe',
    'Subscribe',
    [
        'NewsletterSubscribe' => 'showForm, createConfirmation, undosubscribe, unsubscribe, doConfirm, createUnsubscribeMail',
    ],
    // non-cacheable actions
    [
        'NewsletterSubscribe' => 'showForm, createConfirmation, undosubscribe, unsubscribe, doConfirm, createUnsubscribeMail',
    ]
);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Zwo3.NewsletterSubscribe',
    'Unsubscribe',
    [
        'NewsletterSubscribe' => 'showUnsubscribeForm, unsubscribe, createUnsubscribeMail',
    ],
    // non-cacheable actions
    [
        'NewsletterSubscribe' => 'showUnsubscribeForm, unsubscribe, createUnsubscribeMail',
    ]
);

$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_newsletter_subscribe_subscribe[subscriptionHash]';
$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_newsletter_subscribe_subscribe[uid]';
$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_newsletter_subscribe_unsubscribe[subscriptionHash]';
$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_newsletter_subscribe_unsubscribe[uid]';
