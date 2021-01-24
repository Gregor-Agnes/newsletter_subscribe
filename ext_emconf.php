<?php

$EM_CONF['newsletter_subscribe'] = array(
	'title' => 'Newsletter Subscribe',
	'description' => 'subscribe and unsubscribe to tt_address, generate static Link to unsubscribe (to use in Newsletter)',
	'category' => 'plugin',
	'author' => 'Gregor Agnes',
	'author_email' => 'ga@zwo3.de',
	'author_company' => 'Gregor Agnes & Markus Cousin GbR',
	'shy' => '',
	'priority' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => '1',
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'version' => '3.1.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '10.4.2-10.4.99',
            'tt_address' => '4.0.0-5.99.99',
            'vhs' => '6.0.0-6.99.99'
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);

