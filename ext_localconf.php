<?php
defined('TYPO3_MODE') || die('Access denied.');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Zwo3.Subscribe',
    'Subscribe',
    [
        'Subscribe' => 'showForm, createConfirmation, undosubscribe, unsubscribe, doConfirm, createUnsubscribeMail',
    ],
    // non-cacheable actions
    [
        'Subscribe' => 'showForm, createConfirmation, undosubscribe, unsubscribe, doConfirm, createUnsubscribeMail',
    ]
);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Zwo3.Subscribe',
    'Unsubscribe',
    [
        'Subscribe' => 'showUnsubscribeForm, unsubscribe, createUnsubscribeMail',
    ],
    // non-cacheable actions
    [
        'Subscribe' => 'showUnsubscribeForm, unsubscribe, createUnsubscribeMail',
    ]
);

$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_subscribe_subscribe[subscriptionHash]';
$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_subscribe_subscribe[uid]';
$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_subscribe_unsubscribe[subscriptionHash]';
$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_subscribe_unsubscribe[uid]';
