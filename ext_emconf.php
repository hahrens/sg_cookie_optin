<?php

$EM_CONF[$_EXTKEY] = [
	'title' => 'sgalinski Cookie Optin',
	'description' => 'This extensions adds a cookie optin for the frontend.',
	'category' => 'module',
	'version' => '1.4.0',
	'state' => 'stable',
	'uploadfolder' => FALSE,
	'createDirs' => '',
	'clearcacheonload' => FALSE,
	'author' => 'Stefan Galinski',
	'author_email' => 'stefan@sgalinski.de',
	'author_company' => 'sgalinski Internet Services (https://www.sgalinski.de)',
	'constraints' => [
		'depends' => [
			'typo3' => '7.6.0-9.5.99',
			'php' => '5.5.0-7.3.99',
		],
		'conflicts' => [
		],
		'suggests' => [
		],
	],
];

