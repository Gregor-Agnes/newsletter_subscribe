<?php
defined('TYPO3_MODE') || die('Access denied.');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Zwo3.NewsletterSubscribe',
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


$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Zwo3\NewsletterSubscribe\SchedulerTask\DeleteUnvalidatedSubscribersTask::class] = [
    'extension' => 'newsletter_subscribe',
    'title' => 'LLL:EXT:newsletter_subscribe/Resources/Private/Language/locallang.xlf:schedulerDeleteUnvalidated.name',
    'description' => 'LLL:EXT:newsletter_subscribe/Resources/Private/Language/locallang.xlf:schedulerDeleteUnvalidated.description',
    'additionalFields' => \Zwo3\NewsletterSubscribe\SchedulerTask\DeleteUnvalidatedSubscribersTaskAdditionalFieldProvider::class
];

