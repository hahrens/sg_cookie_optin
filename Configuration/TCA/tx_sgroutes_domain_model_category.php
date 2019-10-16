<?php

return [
	'ctrl' => [
		'title' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_category',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'searchFields' => 'title',
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,
		'origUid' => 't3_origuid',
		'delete' => 'deleted',
		'hideTable' => TRUE,
		'iconfile' => 'EXT:sg_routes/Resources/Public/Icons/tx_sgroutes_domain_model_route.svg'
	],
	'interface' => [
		'showRecordFieldList' => 'title, description, used_for_automated_routing',
	],
	'types' => [
		'1' => [
			'showitem' => 'title, description, used_for_automated_routing',
		],
	],
	'columns' => [
		't3ver_label' => [
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.versionLabel',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			]
		],
		'title' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_category.title',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			],
		],
		'description' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_route.description',
			'config' => [
				'type' => 'text',
				'eval' => 'trim'
			],
		],
		'used_for_automated_routing' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_category.used_for_automated_routing',
			'config' => [
				'type' => 'check'
			],
		],
	],
];
