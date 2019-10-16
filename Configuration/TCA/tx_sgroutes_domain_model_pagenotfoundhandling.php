<?php

return [
	'ctrl' => [
		'title' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_pagenotfoundhandling',
		'label' => 'function',
		'dividers2tabs' => TRUE,
		'searchFields' => 'function',
		'hideTable' => TRUE,
		'iconfile' => 'EXT:sg_routes/Resources/Public/Icons/tx_sgroutes_domain_model_route.svg'
	],
	'interface' => [
		'showRecordFieldList' => 'function, header, description',
	],
	'types' => [
		'1' => [
			'showitem' => 'function_info, function, header, description',
		],
	],
	'columns' => [
		'function' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_pagenotfoundhandling.function',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim',
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
		'function_info' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_pagenotfoundhandling.function_info',
			'config' => [
				'type' => 'user',
				'userFunc' => 'SGalinski\\SgRoutes\\Backend\\TCAInfoField->render',
				'parameters' => [
					'text' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_pagenotfoundhandling.function_info_text'
				]
			],
		],
		'header' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:sg_routes/Resources/Private/Language/locallang_db.xlf:tx_sgroutes_domain_model_pagenotfoundhandling.header',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim',
				'default' => 'HTTP/1.1 404 Not Found'
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
	],
];
