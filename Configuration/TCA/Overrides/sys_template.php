<?php
defined('TYPO3_MODE') || die();

call_user_func(function()
{
    /**
     * Extension key
     */
    $extensionKey = 'newsletter_subscribe';

    /**
     * Default TypoScript
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        $extensionKey,
        'Configuration/TypoScript',
        'Subscribe'
    );
});