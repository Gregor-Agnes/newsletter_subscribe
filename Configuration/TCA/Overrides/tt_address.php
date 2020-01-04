<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$GLOBALS['TCA']['tt_address']['ctrl']['delete'] = null;

$tempColumns = [
    'data_protection_accepted' => array(
        'exclude' => 0,
        'label' => 'Datenschutzerklärung akzeptiert',
        'config' => array(
            'type' => 'check',
            'default' => 0
        ),
    ),
    'subscription_hash' => array(
        'label' => 'Subscription Hash',
        'exclude' => 1,
        'config' => array(
            'size' => 30,
            'type' => 'input',
            'default' => '',
            'readOnly' =>1
        ),
    ),
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_address',$tempColumns,1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'tt_address',
    'data_protection, subscription_hash');