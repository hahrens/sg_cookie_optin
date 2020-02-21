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

$configuration = [
	'ctrl' => [
		'title' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin',
		'label' => 'header',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'searchFields' => 'header, description, accept_all_text, accept_specific_text, accept_essential_text, 
			essential_title, essential_description, extend_box_link_text, extend_box_link_text_close, 
			extend_table_link_text, extend_table_link_text_close, cookie_name_text, cookie_provider_text, 
			cookie_purpose_text, cookie_lifetime_text, iframe_title, iframe_description, iframe_button_allow_all_text, 
			iframe_button_allow_one_text, iframe_button_load_one_text, iframe_open_settings_text, template_html',
		'delete' => 'deleted',
		'hideTable' => FALSE,
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'iconfile' => 'EXT:sg_cookie_optin/Resources/Public/Icons/tx_sgcookieoptin_domain_model_optin.svg',
		'requestUpdate' => 'template_selection',
	],
	'interface' => [
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, header, description, cookie_lifetime, 
			minify_generated_data, navigation, accept_all_text, accept_specific_text, accept_essential_text, groups, 
			template_html, template_overwritten, template_selection, color_text, color_box, color_headline, 
			color_checkbox, color_checkbox_required, color_button_all, color_button_all_text, color_button_specific, 
			color_button_specific_text, color_button_essential, color_button_essential_text, color_list, 
			color_list_text, essential_title, essential_description, essential_scripts, essential_cookies, 
			extend_box_link_text, extend_box_link_text_close, extend_table_link_text, extend_table_link_text_close, 
			color_button_all_hover, color_button_specific_hover, color_button_essential_hover, color_table, 
			color_table_header_text, color_Table_data_text, color_button_close, color_button_close_hover, 
			color_button_close_text, cookie_name_text, cookie_provider_text, cookie_purpose_text, cookie_lifetime_text, 
			iframe_enabled, iframe_title, iframe_description, iframe_button_allow_all_text, 
			iframe_button_allow_one_text, iframe_button_load_one_text, iframe_open_settings_text, 
			iframe_color_consent_box_background, iframe_color_button_load_one, iframe_color_button_load_one_hover, 
			iframe_color_button_load_one_text, iframe_color_open_settings',
	],
	'types' => [
		'1' => [
			'showitem' => '
				header, description, --palette--;;accept_buttons_texts, --palette--;;link_texts, --palette--;;cookie_texts, navigation, 
				--div--;LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.tab.iframes, 
					iframe_enabled, iframe_title, iframe_description, --palette--;;iframe_texts, --palette--;;iframe_colors,
				--div--;LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.tab.essential, 
					essential_title, essential_description, essential_scripts, essential_cookies,
				--div--;LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.tab.group, 
					groups,
				--div--;LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.tab.color, 
					template_selection, --palette--;;color_general, --palette--;;color_checkbox, --palette--;;color_button,
					--palette--;;color_list, --palette--;;color_table, template_overwritten, template_html, 
				--div--;LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.tab.settings,
					cookie_lifetime, minify_generated_data',
		],
	],
	'palettes' => [
		'accept_buttons_texts' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.accept_buttons_texts',
			'showitem' => 'accept_all_text, accept_specific_text, accept_essential_text'
		],
		'link_texts' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.link_texts',
			'showitem' => 'extend_box_link_text, extend_box_link_text_close, --linebreak--, 
				extend_table_link_text, extend_table_link_text_close'
		],
		'cookie_texts' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.cookie_texts',
			'showitem' => 'cookie_name_text, cookie_provider_text, --linebreak--,
			 	cookie_purpose_text, cookie_lifetime_text'
		],
		'color_general' => [
			'showitem' => 'color_box, color_headline, color_text'
		],
		'color_checkbox' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.colors_checkboxes',
			'showitem' => 'color_checkbox_required, color_checkbox'
		],
		'color_button' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.colors_buttons',
			'showitem' => 'color_button_close, color_button_close_hover, color_button_close_text, --linebreak--,
				color_button_all, color_button_all_hover, color_button_all_text, --linebreak--,
				color_button_specific, color_button_specific_hover, color_button_specific_text, --linebreak--,
				color_button_essential, color_button_essential_hover, color_button_essential_text'
		],
		'color_list' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.colors_lists',
			'showitem' => 'color_list, color_list_text'
		],
		'color_table' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.colors_tables',
			'showitem' => 'color_table, color_table_header_text, color_Table_data_text'
		],
		'iframe_texts' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_texts',
			'showitem' => 'iframe_button_allow_all_text, iframe_button_allow_one_text, --linebreak--, 
				iframe_button_load_one_text, iframe_open_settings_text'
		],
		'iframe_colors' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_colors',
			'showitem' => 'iframe_color_consent_box_background, --linebreak--, 
				iframe_color_button_load_one, iframe_color_button_load_one_hover, iframe_color_button_load_one_text, --linebreak--,
				iframe_color_open_settings'
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
				'placeholder' => 'Datenschutzeinstellungen',
				'eval' => 'trim, required'
			],
		],
		'description' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.description',
			'config' => [
				'type' => 'text',
				'default' => 'Auf unserer Webseite werden Cookies verwendet. Einige davon werden zwingend benötigt, während es uns andere ermöglichen, Ihre Nutzererfahrung auf unserer Webseite zu verbessern.',
				'placeholder' => 'Auf unserer Webseite werden Cookies verwendet. Einige davon werden zwingend benötigt, während es uns andere ermöglichen, Ihre Nutzererfahrung auf unserer Webseite zu verbessern.',
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
				'placeholder' => 'Alle akzeptieren',
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
				'placeholder' => 'Speichern & schließen',
				'eval' => 'trim, required'
			],
		],
		'accept_essential_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.accept_essential_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Nur essentielle Cookies akzeptieren',
				'placeholder' => 'Nur essentielle Cookies akzeptieren',
				'eval' => 'trim, required'
			],
		],
		'extend_box_link_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.extend_box_link_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Weitere Informationen anzeigen',
				'placeholder' => 'Weitere Informationen anzeigen',
				'eval' => 'trim, required'
			],
		],
		'extend_box_link_text_close' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.extend_box_link_text_close',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Weitere Informationen verstecken',
				'placeholder' => 'Weitere Informationen verstecken',
				'eval' => 'trim, required'
			],
		],
		'extend_table_link_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.extend_table_link_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Cookie-Informationen anzeigen',
				'placeholder' => 'Cookie-Informationen anzeigen',
				'eval' => 'trim, required'
			],
		],
		'extend_table_link_text_close' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.extend_table_link_text_close',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Cookie-Informationen verstecken',
				'placeholder' => 'Cookie-Informationen verstecken',
				'eval' => 'trim, required'
			],
		],
		'cookie_name_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.cookie_name_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Name',
				'placeholder' => 'Name',
				'eval' => 'trim, required'
			],
		],
		'cookie_provider_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.cookie_provider_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Anbieter',
				'placeholder' => 'Anbieter',
				'eval' => 'trim, required'
			],
		],
		'cookie_purpose_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.cookie_purpose_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Zweck',
				'placeholder' => 'Zweck',
				'eval' => 'trim, required'
			],
		],
		'cookie_lifetime_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.cookie_lifetime_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Laufzeit',
				'placeholder' => 'Laufzeit',
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
				'maxitems' => 2,
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
					'showPossibleLocalizationRecords' => TRUE,
					'showRemovedLocalizationRecords' => FALSE,
					'showAllLocalizationLink' => TRUE,
				],
				'maxitems' => 99999,
			],
		],
		'color_box' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_box',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'placeholder' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
		'color_headline' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_headline',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#373737',
				'placeholder' => '#373737',
				'eval' => 'trim, required'
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
				'placeholder' => '#373737',
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
				'placeholder' => '#7B9B2C',
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
				'placeholder' => '#A5A5A5',
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
				'placeholder' => '#7B9B2C',
				'eval' => 'trim, required'
			],
		],
		'color_button_all_hover' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_all_hover',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#8FAF2D',
				'placeholder' => '#8FAF2D',
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
				'placeholder' => '#FFFFFF',
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
				'default' => '#A5A5A5',
				'placeholder' => '#A5A5A5',
				'eval' => 'trim, required'
			],
		],
		'color_button_specific_hover' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_specific_hover',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#D7D7D7',
				'placeholder' => '#D7D7D7',
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
				'placeholder' => '#FFFFFF',
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
				'placeholder' => '#A5A5A5',
				'eval' => 'trim, required'
			],
		],
		'color_button_essential_hover' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_essential_hover',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#D7D7D7',
				'placeholder' => '#D7D7D7',
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
				'placeholder' => '#FFFFFF',
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
				'placeholder' => '#A5A5A5',
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
				'placeholder' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
		'color_table' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_table',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'placeholder' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
		'color_table_header_text' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_table_header_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#373737',
				'placeholder' => '#373737',
				'eval' => 'trim, required'
			],
		],
		'color_Table_data_text' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_Table_data_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#373737',
				'placeholder' => '#373737',
				'eval' => 'trim, required'
			],
		],
		'color_button_close' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_close',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#A5A5A5',
				'placeholder' => '#A5A5A5',
				'eval' => 'trim, required'
			],
		],
		'color_button_close_hover' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_close_hover',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#D7D7D7',
				'placeholder' => '#D7D7D7',
				'eval' => 'trim, required'
			],
		],
		'color_button_close_text' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_close_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'placeholder' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
		'essential_title' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.essential_title',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Essentiell',
				'placeholder' => 'Essentiell',
				'eval' => 'trim, required'
			],
		],
		'essential_description' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.essential_description',
			'config' => [
				'type' => 'text',
				'default' => 'Essentielle Cookies werden für grundlegende Funktionen der Webseite benötigt. Dadurch ist gewährleistet, dass die Webseite einwandfrei funktioniert.',
				'placeholder' => 'Essentielle Cookies werden für grundlegende Funktionen der Webseite benötigt. Dadurch ist gewährleistet, dass die Webseite einwandfrei funktioniert.',
				'eval' => 'trim'
			],
		],
		'essential_scripts' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.essential_scripts',
			'config' => [
				'type' => 'inline',
				'foreign_table' => 'tx_sgcookieoptin_domain_model_script',
				'foreign_field' => 'parent_optin',
				'foreign_sortby' => 'sorting',
				'appearance' => [
					'showPossibleLocalizationRecords' => TRUE,
					'showRemovedLocalizationRecords' => FALSE,
					'showAllLocalizationLink' => TRUE,
				],
				'maxitems' => 99999,
			],
		],
		'essential_cookies' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.essential_cookies',
			'config' => [
				'type' => 'inline',
				'foreign_table' => 'tx_sgcookieoptin_domain_model_cookie',
				'foreign_field' => 'parent_optin',
				'foreign_sortby' => 'sorting',
				'appearance' => [
					'showPossibleLocalizationRecords' => TRUE,
					'showRemovedLocalizationRecords' => FALSE,
					'showAllLocalizationLink' => TRUE,
				],
				'maxitems' => 99999,
			],
		],
		'iframe_enabled' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_enabled',
			'config' => [
				'type' => 'check',
				'default' => '0',
			],
		],
		'iframe_title' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_title',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Externe Inhalte',
				'placeholder' => 'Externe Inhalte',
				'eval' => 'trim, required'
			],
		],
		'iframe_description' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_description',
			'config' => [
				'type' => 'text',
				'default' => 'Wir verwenden auf unserer Website externe Inhalte, um Ihnen zusätzliche Informationen anzubieten.',
				'placeholder' => 'Wir verwenden auf unserer Website externe Inhalte, um Ihnen zusätzliche Informationen anzubieten.',
				'eval' => 'trim'
			],
		],
		'iframe_button_allow_all_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_button_allow_all_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Alle externen Inhalte erlauben',
				'placeholder' => 'Alle externen Inhalte erlauben',
				'eval' => 'trim, required'
			],
		],
		'iframe_button_allow_one_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_button_allow_one_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Einmalig erlauben',
				'placeholder' => 'Einmalig erlauben',
				'eval' => 'trim, required'
			],
		],
		'iframe_button_load_one_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_button_load_one_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Externen Inhalt laden',
				'placeholder' => 'Externen Inhalt laden',
				'eval' => 'trim, required'
			],
		],
		'iframe_open_settings_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_open_settings_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Einstellungen anzeigen',
				'placeholder' => 'Einstellungen anzeigen',
				'eval' => 'trim, required'
			],
		],
		'iframe_color_consent_box_background' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_color_consent_box_background',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#D6D6D6',
				'placeholder' => '#D6D6D6',
				'eval' => 'trim, required'
			],
		],
		'iframe_color_button_load_one' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_color_button_load_one',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#7B9B2C',
				'placeholder' => '#7B9B2C',
				'eval' => 'trim, required'
			],
		],
		'iframe_color_button_load_one_hover' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_color_button_load_one_hover',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#8FAF2D',
				'placeholder' => '#8FAF2D',
				'eval' => 'trim, required'
			],
		],
		'iframe_color_button_load_one_text' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_color_button_load_one_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'placeholder' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
		'iframe_color_open_settings' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_color_open_settings',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#373737',
				'placeholder' => '#373737',
				'eval' => 'trim, required'
			],
		],
		'cookie_lifetime' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.cookie_lifetime',
			'config' => [
				'type' => 'input',
				'default' => '365',
				'placeholder' => '365',
				'eval' => 'trim, int, required'
			],
		],
		'minify_generated_data' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.minify_generated_data',
			'config' => [
				'type' => 'check',
				'default' => '1',
			],
		],
		'template_html' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.template_html',
			'config' => [
				'type' => 'text',
				'renderType' => 't3editor',
				'format' => 'html',
				'eval' => 'trim'
			],
		],
		'template_overwritten' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.template_overwritten',
			'config' => [
				'type' => 'check',
				'default' => '0',
			],
		],
		'template_selection' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.template_selection',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'minitems' => 1,
				'items' => [
					['Default', 0],
					['New One', 1],
				],
			],
		],
	],
];

// The color picker isn't available in TYPO3 7.X
if (TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < 8000000) {
	unset(
		$configuration['columns']['color_box']['config']['renderType'],
		$configuration['columns']['color_headline']['config']['renderType'],
		$configuration['columns']['color_text']['config']['renderType'],
		$configuration['columns']['color_checkbox']['config']['renderType'],
		$configuration['columns']['color_checkbox_required']['config']['renderType'],
		$configuration['columns']['color_button_all']['config']['renderType'],
		$configuration['columns']['color_button_all_hover']['config']['renderType'],
		$configuration['columns']['color_button_all_text']['config']['renderType'],
		$configuration['columns']['color_button_specific']['config']['renderType'],
		$configuration['columns']['color_button_specific_hover']['config']['renderType'],
		$configuration['columns']['color_button_specific_text']['config']['renderType'],
		$configuration['columns']['color_button_essential']['config']['renderType'],
		$configuration['columns']['color_button_essential_hover']['config']['renderType'],
		$configuration['columns']['color_button_essential_text']['config']['renderType'],
		$configuration['columns']['color_button_close']['config']['renderType'],
		$configuration['columns']['color_button_close_hover']['config']['renderType'],
		$configuration['columns']['color_button_close_text']['config']['renderType'],
		$configuration['columns']['color_list']['config']['renderType'],
		$configuration['columns']['color_list_text']['config']['renderType'],
		$configuration['columns']['color_table']['config']['renderType'],
		$configuration['columns']['color_table_header_text']['config']['renderType'],
		$configuration['columns']['color_Table_data_text']['config']['renderType'],
		$configuration['columns']['iframe_color_consent_box_background']['config']['renderType'],
		$configuration['columns']['iframe_color_button_load_one']['config']['renderType'],
		$configuration['columns']['iframe_color_button_load_one_hover']['config']['renderType'],
		$configuration['columns']['iframe_color_button_load_one_text']['config']['renderType'],
		$configuration['columns']['iframe_color_open_settings']['config']['renderType']
	);
}

return $configuration;
