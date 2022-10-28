<?php
//https://docs.typo3.org/m/typo3/reference-coreapi/11.5/en-us/ApiOverview/Icon/Index.html
return [
    // icon identifier
    'zwo3_newslettersubscribe-plugin-subscribe' => [
        // icon provider class
       'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        // the source SVG for the SvgIconProvider
       'source' => 'EXT:newsletter_subscribe/Resources/Public/images/Extension.svg'
   ],
   'zwo3_newslettersubscribe-plugin-unsubscribe' => [
        // icon provider class
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            // the source SVG for the SvgIconProvider
        'source' => 'EXT:newsletter_subscribe/Resources/Public/images/Extension.svg'
    ]
];