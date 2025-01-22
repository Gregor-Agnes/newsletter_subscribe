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
    'version' => '7.0.7',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.00-13.4.99',
            'tt_address' => '8.0.0 - 9.99.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];