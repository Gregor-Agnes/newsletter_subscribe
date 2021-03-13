<?php

$EM_CONF[$_EXTKEY] = [
	'title' => 'Newsletter Subscribe',
	'description' => 'subscribe and unsubscribe to tt_address, generate static Link to unsubscribe (to use in Newsletter)',
	'category' => 'plugin',
	'author' => 'Gregor Agnes',
	'author_email' => 'ga@zwo3.de',
	'author_company' => 'Gregor Agnes & Markus Cousin GbR',
	'state' => 'stable',
	'clearCacheOnLoad' => 0,
	'version' => '3.2.2',
	'constraints' => [
		'depends' => [
			'typo3' => '10.4.2-10.4.99',
            'tt_address' => '4.0.0-5.99.99'
        ],
    ],
];

