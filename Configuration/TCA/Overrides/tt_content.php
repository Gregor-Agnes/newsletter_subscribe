<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Zwo3.NewsletterSubscribe',
    'Subscribe',
    'Subscribe to Addresslist',
    'EXT:newsletter_subscribe/Resources/Public/Gfx/Extension.png'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Zwo3.NewsletterSubscribe',
    'Unsubscribe',
    'Unsubscribe from Addresslist',
    'EXT:newsletter_subscribe/Resources/Public/Gfx/Extension.png'
);


$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['newsletter_subscribe_subscribe'] = 'layout,recursive,select_key,pages';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['newsletter_subscribe_subscribe'] = 'pi_flexform';

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['newsletter_subscribe_subscribe'] = 'layout,recursive,select_key,pages';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['newsletter_subscribe_unsubscribe'] = 'pi_flexform';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'newsletter_subscribe_subscribe',
    'FILE:EXT:newsletter_subscribe/Configuration/FlexForm/flexform_subscribe.xml',
    '*'
);\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'newsletter_subscribe_unsubscribe',
    'FILE:EXT:newsletter_subscribe/Configuration/FlexForm/flexform_unsubscribe.xml',
    '*'
);