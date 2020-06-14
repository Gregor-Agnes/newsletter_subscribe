<?php
defined('TYPO3_MODE') || die('Access denied.');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Zwo3.NewsletterSubscribe',
    'Subscribe',
    [
        'Subscribe' => 'showForm, createConfirmation, undosubscribe, unsubscribe, doConfirm, createUnsubscribeMail, refreshCaptchaImage',
    ],
    // non-cacheable actions
    [
        'Subscribe' => 'showForm, createConfirmation, undosubscribe, unsubscribe, doConfirm, createUnsubscribeMail, refreshCaptchaImage',
    ]
);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Zwo3.NewsletterSubscribe',
    'Unsubscribe',
    [
        'Subscribe' => 'showUnsubscribeForm, unsubscribe, createUnsubscribeMail',
    ],
    // non-cacheable actions
    [
        'Subscribe' => 'showUnsubscribeForm, unsubscribe, createUnsubscribeMail',
    ]
);

$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_newslettersubscribe_subscribe[subscriptionHash]';
$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_newslettersubscribe_subscribe[uid]';
$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_newslettersubscribe_unsubscribe[subscriptionHash]';
$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_newslettersubscribe_unsubscribe[uid]';

