<?php

return [
	'ctrl' => [
		'title' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_log',
		'label' => 'request_url',
		'crdate' => 'crdate',
		'dividers2tabs' => TRUE,
		'searchFields' => 'source_url, destination_url, redirect_code, description, categories, request_url, redirect_url',
		'hideTable' => TRUE,
		'iconfile' => 'EXT:sg_routes/Resources/Public/Icons/tx_sgroutes_domain_model_route.svg'
	],
	'interface' => [
		'showRecordFieldList' => 'use_regular_expression, source_url, destination_url, redirect_url_parameters, redirect_code, description, categories, request_url, redirect_url, execution_duration',
	],
	'types' => [
		'1' => [
			'showitem' => 'use_regular_expression, source_url, destination_url, redirect_url_parameters, redirect_code, description, categories, request_url, redirect_url, execution_duration',
		],
	],
	'columns' => [
		'use_regular_expression' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_route.use_regular_expression',
			'config' => [
				'type' => 'check'
			],
		],
		'source_url' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_log.source_url',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			],
		],
		'destination_url' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_log.destination_url',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			],
		],
		'redirect_url_parameters' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_route.redirect_url_parameters',
			'config' => [
				'type' => 'check'
			],
		],
		'redirect_code' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_route.redirect_code',
			'config' => [
				'type' => 'select',
				'size' => 1,
				'items' => [
					['LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_route.permanently', '301'],
					['LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_route.temporary', '302']
				]
			],
		],
		'description' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_route.description',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			],
		],
		'categories' => [
			'exclude' => 1,
			'label' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_route.categories',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			],
		],
		'request_url' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_log.request_url',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			],
		],
		'redirect_url' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_log.redirect_url',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			],
		],
		'execution_duration' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_log.execution_duration',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			],
		],
	],
];
