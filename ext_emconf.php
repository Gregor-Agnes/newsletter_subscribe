<?php

$EM_CONF['newsletter_subscribe'] = [
    'title' => 'Newsletter Subscribe',
    'description' => 'subscribe and unsubscribe to tt_address, generate static Link to unsubscribe (to use in Newsletter), remove unvalidated subscriptions with scheduler task',
    'category' => 'plugin',
    'author' => 'Gregor Agnes',
    'author_email' => 'ga@zwo3.de',
    'author_company' => 'Gregor Agnes & Markus Cousin GbR',
    'state' => 'stable',
    'clearCacheOnLoad' => 0,
    'version' => '3.7.2',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.16-11.5.99',
            'tt_address' => '6.1.0-6.99.99'
        ],
    ],
];

