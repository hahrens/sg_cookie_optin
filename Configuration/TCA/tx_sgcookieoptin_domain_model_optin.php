<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) sgalinski Internet Services (https://www.sgalinski.de)
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

return [
	'ctrl' => [
		'title' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin',
		'label' => 'header',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'searchFields' => 'header, description, accept_all_text, accept_specific_text, accept_essential_text',
		'delete' => 'deleted',
		'hideTable' => FALSE,
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'iconfile' => 'EXT:sg_cookie_optin/Resources/Public/Icons/tx_sgcookieoptin_domain_model_optin.svg'
	],
	'interface' => [
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, header, description, 
			navigation, accept_all_text, accept_specific_text, accept_essential_text, groups, 
			color_text, color_checkbox, color_checkbox_required, color_button_all, color_button_all_text, 
			color_button_specific, color_button_specific_text, color_button_essential, color_button_essential_text,
			color_list, color_list_text',
	],
	'types' => [
		'1' => [
			'showitem' => '--palette--;;language, header, accept_all_text, accept_specific_text, accept_essential_text, description, navigation, 
				--div--;LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.groups, groups,
				--div--;LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.tab.color, color_text, 
				--palette--;;color_checkbox, --palette--;;color_button, --palette--;;color_list,',
		],
	],
	'palettes' => [
		'language' => [
			'showitem' => 'sys_language_uid, l10n_parent'
		],
		'color_checkbox' => [
			'showitem' => 'color_checkbox, color_checkbox_required'
		],
		'color_button' => [
			'showitem' => 'color_button_all, color_button_all_text, --linebreak--,
				color_button_specific, color_button_specific_text, --linebreak--,
				color_button_essential, color_button_essential_text'
		],
		'color_list' => [
			'showitem' => 'color_list, color_list_text'
		],
	],
	'columns' => [
		'pid' => [
			'exclude' => FALSE,
			'label' => 'PID',
			'config' => [
				'type' => 'none',
			]
		],
		'sys_language_uid' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'special' => 'languages',
				'default' => 0,
				'items' => [
					[
						'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages',
						-1,
						'flags-multiple'
					]
				]
			]
		],
		'l10n_parent' => [
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => TRUE,
			'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => [
					['', 0]
				],
				'foreign_table' => 'tx_sgcookieoptin_domain_model_optin',
				'foreign_table_where' => 'AND tx_sgcookieoptin_domain_model_optin.uid=###REC_FIELD_l10n_parent### AND tx_sgcookieoptin_domain_model_optin.sys_language_uid IN (-1,0)',
				'default' => 0
			]
		],
		'l10n_diffsource' => [
			'config' => [
				'type' => 'passthrough',
				'default' => ''
			]
		],
		'header' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.header',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Datenschutzeinstellungen',
				'eval' => 'trim, required'
			],
		],
		'description' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.description',
			'config' => [
				'type' => 'text',
				'default' => 'Wir nutzen Cookies auf unserer Website. Einige von ihnen sind essenziell, während andere uns helfen, diese Website und Ihre Erfahrung zu verbessern.',
				'eval' => 'trim'
			],
		],
		'accept_all_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.accept_all_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Alle akzeptieren',
				'eval' => 'trim, required'
			],
		],
		'accept_specific_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.accept_specific_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Speichern & schließen',
				'eval' => 'trim, required'
			],
		],
		'accept_essential_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.accept_essential_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Nur essenzielle Cookies akzeptieren',
				'eval' => 'trim, required'
			],
		],
		'navigation' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.navigation',
			'config' => [
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'pages',
				'minitems' => 0,
				'maxitems' => 3,
				'wizards' => [
					'suggest' => [
						'type' => 'suggest'
					]
				],
			],
		],
		'groups' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.groups',
			'config' => [
				'type' => 'inline',
				'foreign_table' => 'tx_sgcookieoptin_domain_model_group',
				'foreign_field' => 'parent_optin',
				'foreign_sortby' => 'sorting',
				'appearance' => [
					'expandSingle' => TRUE,
				],
				'maxitems' => 99999,
			],
		],
		'color_text' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#373737',
				'eval' => 'trim, required'
			],
		],
		'color_checkbox' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_checkbox',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#7B9B2C',
				'eval' => 'trim, required'
			],
		],
		'color_checkbox_required' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_checkbox_required',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#A5A5A5',
				'eval' => 'trim, required'
			],
		],
		'color_button_all' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_all',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#7B9B2C',
				'eval' => 'trim, required'
			],
		],
		'color_button_all_text' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_all_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
		'color_button_specific' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_specific',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#7B9B2C',
				'eval' => 'trim, required'
			],
		],
		'color_button_specific_text' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_specific_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
		'color_button_essential' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_essential',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#A5A5A5',
				'eval' => 'trim, required'
			],
		],
		'color_button_essential_text' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_essential_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
		'color_list' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_list',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#A5A5A5',
				'eval' => 'trim, required'
			],
		],
		'color_list_text' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_list_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
	],
];
