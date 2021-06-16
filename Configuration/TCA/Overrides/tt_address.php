<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$GLOBALS['TCA']['tt_address']['ctrl']['delete'] = null;

$tempColumns = [
    'data_protection_accepted' => [
        'label' => 'LLL:EXT:newsletter_subscribe/Resources/Private/Language/locallang_db.xlf:tx_zwo3_newslettersubscribe_subscribe.data_protection_accepted',
        'exclude' => 0,
        'config' => [
            'type' => 'check',
            'default' => 0
        ],
    ],
    'subscription_hash' => [
        'label' => 'LLL:EXT:newsletter_subscribe/Resources/Private/Language/locallang_db.xlf:tx_zwo3_newslettersubscribe_subscribe.subscription_hash',
        'exclude' => 1,
        'config' => [
            'size' => 30,
            'type' => 'input',
            'default' => '',
            'readOnly' => 1
        ],
    ],
    'last_hit' => [
        'label' => 'LLL:EXT:newsletter_subscribe/Resources/Private/Language/locallang_db.xlf:tx_zwo3_newslettersubscribe_subscribe.last_hit',
        'exclude' => 1,
        'config' => [
            'type' => 'input',
            'renderType' => 'inputDateTime',
            'eval' => 'datetime',
            'readOnly' => 1
        ]
    ],
    'hit_number' => [
        'label' => 'LLL:EXT:newsletter_subscribe/Resources/Private/Language/locallang_db.xlf:tx_zwo3_newslettersubscribe_subscribe.hit_number',
        'exclude' => 1,
        'config' => [
            'size' => 30,
            'type' => 'input',
            'default' => '',
            'readOnly' => 1
        ],
    ],
    'salutation' => [
        'label' =>  'LLL:EXT:newsletter_subscribe/Resources/Private/Language/locallang_db.xlf:tx_zwo3_newslettersubscribe_subscribe.salutation',
        'exclude' => 1,
        'config' => [
            'size' => 100,
            'type' => 'input',
            'default' => ''
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_address', $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'tt_address',
    'data_protection_accepted, subscription_hash, last_hit, hit_number, salutation'
);