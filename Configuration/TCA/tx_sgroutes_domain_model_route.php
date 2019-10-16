<?php


if (\SGalinski\SgRoutes\Service\LicensingService::checkKey()) {
	$showFields = 'use_regular_expression, regular_expression_info, --palette--;LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_route.palettes.source_url;source_url, --palette--;LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_route.palettes.destination_url;destination_url, redirect_url_parameters, redirect_code, description, categories';
} else {
	$showFields = '--palette--;LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_route.palettes.source_url;source_url, --palette--;LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_route.palettes.destination_url;destination_url, redirect_url_parameters, redirect_code, description, categories';
}

return [
	'ctrl' => [
		'title' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_route',
		'label' => 'description',
		'label_alt' => 'source_url, destination_url',
		'label_alt_force' => TRUE,
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'searchFields' => 'source_url, destination_url, redirect_code, description',
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,
		'origUid' => 't3_origuid',
		'delete' => 'deleted',
		'hideTable' => TRUE,
		'sortby' => 'sorting',
		'iconfile' => 'EXT:sg_routes/Resources/Public/Icons/tx_sgroutes_domain_model_route.svg'
	],
	'interface' => [
		'showRecordFieldList' => 'use_regular_expression, source_url, source_url_case_sensitive, destination_url, redirect_url_parameters, redirect_code, description',
	],
	'types' => [
		'1' => [
			'showitem' => $showFields,
		],
	],
	'palettes' => [
		'source_url' => [
			'showitem' => 'source_url,source_url_case_sensitive'
		],
		'destination_url' => [
			'showitem' => 'destination_url,destination_language'
		]
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
		'use_regular_expression' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_route.use_regular_expression',
			'config' => [
				'type' => 'check'
			],
		],
		'regular_expression_info' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_route.regular_expression_info',
			'config' => [
				'type' => 'user',
				'userFunc' => 'SGalinski\\SgRoutes\\Backend\\TCAInfoField->render',
				'parameters' => [
					'links' => [
						[
							'https://www.cheatography.com/davechild/cheat-sheets/regular-expressions/',
							'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_route.regular_expression_info_cheatsheet'
						],
						[
							'http://www.phpliveregex.com/',
							'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_route.regular_expression_info_tester'
						]
					]
				]
			],
		],
		'source_url' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_route.source_url',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim, required'
			],
		],
		'source_url_case_sensitive' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_route.source_url_case_sensitive',
			'config' => [
				'type' => 'check',
				'default' => '0'
			],
			'displayCond' => 'FIELD:use_regular_expression:!=:1'
		],
		'destination_url' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_route.destination_url',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim, required',
				'wizards' => [
					'link' => [
						'type' => 'popup',
						'title' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_pagenotfoundhandling.functionBrowsePages',
						'icon' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_list.gif',
						'module' => [
							'name' => 'wizard_link',
						],
						'params' => [
							'blindLinkOptions' => 'mail,file,folder,url',
							'blindLinkFields' => 'target,title,class,params',
						],
						'JSopenParams' => 'height=800,width=600,status=0,menubar=0,scrollbars=1'
					]
				],
				'softref' => 'typolink'
			],
		],
		'destination_language' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_route.destination_language',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'size' => 1,
				'special' => 'languages'
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
				'type' => 'text',
				'eval' => 'trim'
			],
		],
		'categories' => [
			'exclude' => 1,
			'label' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_route.categories',
			'config' => [
				'type' => 'select',
				'autoSizeMax' => 50,
				'maxitems' => 9999,
				'size' => 10,
				'foreign_table' => 'tx_sgroutes_domain_model_category',
				'foreign_table_where' => 'AND tx_sgroutes_domain_model_category.pid = ###CURRENT_PID###',
				'MM' => 'tx_sgroutes_domain_model_route_category',
				'renderType' => 'selectMultipleSideBySide',
				'wizards' => [
					'_PADDING' => 5,
					'_VALIGN' => 'middle',
					'_VERTICAL' => 0,
					'edit' => [
						'type' => 'popup',
						'title' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_category.edit',
						'module' => [
							'name' => 'wizard_edit',
						],
						'params' => [
							'table' => 'tx_sgroutes_domain_model_category'
						],
						'popup_onlyOpenIfSelected' => 1,
						'icon' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_edit.gif',
						'JSopenParams' => 'height=500,width=580,status=0,menubar=0,scrollbars=1'
					],
					'add' => [
						'type' => 'script',
						'title' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_category.new',
						'icon' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_add.gif',
						'params' => [
							'table' => 'tx_sgroutes_domain_model_category',
							'setValue' => 'prepend'
						],
						'module' => [
							'name' => 'wizard_add'
						]
					],
				],
			],
		],
		'pid' => [
			'label' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_route.pid',
			'config' => [
				'type' => 'passthrough'
			],
		],
		'crdate' => [
			'label' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_route.crdate',
			'config' => [
				'type' => 'passthrough'
			],
		],
		'tstamp' => [
			'label' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_route.tstamp',
			'config' => [
				'type' => 'passthrough'
			],
		],
	],
];
