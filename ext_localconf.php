<?php
defined('TYPO3_MODE') || die('Access denied.');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Zwo3.Subscribe',
    'Subscribe',
    [
        'Subscribe' => 'showForm, createConfirmation, unsubscribe, doConfirm, createUnsubscribeMail',
    ],
    // non-cacheable actions
    [
        'Subscribe' => 'showForm, createConfirmation, unsubscribe, doConfirm, createUnsubscribeMail',
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