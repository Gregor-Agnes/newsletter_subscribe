<?php
defined('TYPO3_MODE') || die();

$GLOBALS['TCA']['tt_address']['ctrl']['delete'] = null;

$tempColumns = [
    'data_protection_accepted' => [
        'exclude' => true,
        'label' => 'Datenschutzerklärung akzeptiert',
        'config' => [
            'type' => 'check',
            'default' => 0
        ],
    ],
    'subscription_hash' => [
        'label' => 'Subscription Hash',
        'exclude' => true,
        'config' => [
            'size' => 30,
            'type' => 'input',
            'default' => '',
            'readOnly' => true
        ],
    ],
    'last_hit' => [
        'label' => 'Last Hit',
        'exclude' => true,
        'config' => [
            'type' => 'input',
            'renderType' => 'inputDateTime',
            'eval' => 'datetime',
            'readOnly' => true
        ]
    ],
    'hit_number' => [
        'label' => 'Number of Hits',
        'exclude' => true,
        'config' => [
            'size' => 30,
            'type' => 'input',
            'default' => '',
            'readOnly' => true
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_address', $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'tt_address',
    'data_protection_accepted, subscription_hash, last_hit, hit_number'
);