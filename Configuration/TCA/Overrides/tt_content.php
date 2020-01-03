<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Zwo3.Subscribe',
    'Subscribe',
    'Subscribe to Addresslist',
    'EXT:subscribe/Resources/Public/Gfx/Extension.svg'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Zwo3.Subscribe',
    'Unsubscribe',
    'Unsubscribe from Addresslist',
    'EXT:subscribe/Resources/Public/Gfx/Extension.svg'
);

$pluginSignature = 'subscribe_subscribe';

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,recursive,select_key';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'subscribe_subscribe',
    'FILE:EXT:subscribe/Configuration/FlexForm/flexform_subscribe.xml',
    '*'
);