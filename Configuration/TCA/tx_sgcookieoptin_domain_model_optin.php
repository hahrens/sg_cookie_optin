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
			cookie_purpose_text, cookie_lifetime_text, iframe_title, iframe_description, iframe_cookies, iframe_button_allow_all_text,
			iframe_button_allow_one_text, iframe_button_load_one_text, iframe_open_settings_text, iframe_whitelist_regex, template_html,
			banner_html, banner_button_accept_text, banner_button_settings_text, banner_description,
			save_confirmation_text',
		'delete' => 'deleted',
		'hideTable' => FALSE,
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'iconfile' => 'EXT:sg_cookie_optin/Resources/Public/Icons/tx_sgcookieoptin_domain_model_optin.svg',
		'requestUpdate' => 'template_selection, banner_selection, iframe_selection, iframe_replacement_selection',
	],
	'interface' => [],
	'types' => [
		'1' => [
			'showitem' => '
				--div--;LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.tab.textAndMenu,
					header, description, save_confirmation_text, --palette--;;accept_buttons_texts,
					--palette--;;link_texts, --palette--;;cookie_texts, navigation,
				--div--;LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.tab.color,
					--palette--;;template, --palette--;;color_general, --palette--;;color_notification,
					--palette--;;color_checkbox, --palette--;;color_button, --palette--;;color_list,
					--palette--;;color_table,
				--div--;LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.tab.iframes,
					iframe_enabled, iframe_title, iframe_description, iframe_cookies, --palette--;;iframe_texts,
					--palette--;;iframe_colors, --palette--;;iframe_template, --palette--;;iframe_replacement_template,
					--palette--;;iframe_whitelist,
				--div--;LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.tab.banner,
					--palette--;;banner_general, --palette--;;banner_general_colors,
					--palette--;;banner_settings_button, --palette--;;banner_accept_button,
					--palette--;;banner_template,
				--div--;LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.tab.essential,
					essential_title, essential_description, essential_scripts, essential_cookies,
				--div--;LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.tab.group,
					groups,
				--div--;LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.tab.settings,
					--palette--;;cookie_lifetime_settings, minify_generated_data, activate_testing_mode, disable_powered_by, set_cookie_for_domain',
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
			'showitem' => 'color_full_box, color_full_headline, color_full_text, --linebreak--, color_box, color_headline, color_text'
		],
		'color_notification' =>[
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_notification',
			'showitem' => 'color_confirmation_background, color_confirmation_text'
		],
		'color_checkbox' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.colors_checkboxes',
			'showitem' => 'color_checkbox_required, color_checkbox'
		],
		'color_button' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.colors_buttons',
			'showitem' => 'show_button_close, --linebreak--,
				color_button_close, color_button_close_hover, color_button_close_text, --linebreak--,
				color_full_button_close, color_full_button_close_hover, color_full_button_close_text, --linebreak--,
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
			'showitem' => 'color_table_header, color_table_header_text, --linebreak--,
				color_table, color_Table_data_text'
		],
		'template' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.template',
			'showitem' => 'template_selection, template_overwritten, --linebreak--,
				template_html'
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
		'iframe_template' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_template',
			'showitem' => 'iframe_selection, iframe_overwritten, --linebreak--,
				iframe_html'
		],
		'iframe_whitelist' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_whitelist',
			'showitem' => 'iframe_whitelist_selection, iframe_whitelist_overwritten, --linebreak--,
			iframe_whitelist_regex'
		],
		'iframe_replacement_template' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_replacement_template',
			'showitem' => 'iframe_replacement_selection, iframe_replacement_overwritten, --linebreak--,
				iframe_replacement_html'
		],
		'banner_general' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.palette.banner_general',
			'showitem' => 'banner_enable, --linebreak--, banner_position,
				banner_description'
		],
		'banner_general_colors' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.palette.banner_general_colors',
			'showitem' => 'banner_color_box, banner_color_text, banner_color_link_text'
		],
		'banner_settings_button' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.palette.banner_settings_button',
			'showitem' => 'banner_show_settings_button, --linebreak--,
				banner_button_settings_text, --linebreak--,
				banner_color_button_settings, banner_color_button_settings_hover, banner_color_button_settings_text'
		],
		'banner_accept_button' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.palette.banner_accept_button',
			'showitem' => 'banner_button_accept_text, --linebreak--,
				banner_color_button_accept, banner_color_button_accept_hover, banner_color_button_accept_text'
		],
		'banner_template' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.palette.banner_template',
			'showitem' => 'banner_selection, banner_overwritten, --linebreak--,
				banner_html'
		],
		'cookie_lifetime_settings' => [
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.palette.cookie_lifetime_settings',
			'showitem' => 'cookie_lifetime, session_only_essential_cookies'
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
		'save_confirmation_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.save_confirmation_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Cookie-Einstellungen erfolgreich gespeichert',
				'placeholder' => 'Cookie-Einstellungen erfolgreich gespeichert',
				'eval' => 'trim, required'
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
			'displayCond' => 'FIELD:template_selection:=:0',
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
		'color_confirmation_background' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_confirmation_background',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#C9FFC9',
				'placeholder' => '#208A20',
				'eval' => 'trim, required'
			]
		],
		'color_confirmation_text' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_confirmation_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#208A20',
				'placeholder' => '#208A20',
				'eval' => 'trim, required'
			]
		],
		'color_checkbox' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_checkbox',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#143D59',
				'placeholder' => '#143D59',
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
				'default' => '#143D59',
				'placeholder' => '#143D59',
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
				'default' => '#2E6B96',
				'placeholder' => '#2E6B96',
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
		'color_table_header' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_table_header',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#F3F3F3',
				'placeholder' => '#F3F3F3',
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
			'displayCond' => 'FIELD:template_selection:=:0',
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_close',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'placeholder' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
		'color_button_close_hover' => [
			'displayCond' => 'FIELD:template_selection:=:0',
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_close_hover',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'placeholder' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
		'color_button_close_text' => [
			'displayCond' => 'FIELD:template_selection:=:0',
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_close_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#373737',
				'placeholder' => '#373737',
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
		'iframe_cookies' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_cookies',
			'config' => [
				'type' => 'inline',
				'foreign_table' => 'tx_sgcookieoptin_domain_model_cookie',
				'foreign_field' => 'parent_iframe',
				'foreign_sortby' => 'sorting',
				'appearance' => [
					'showPossibleLocalizationRecords' => TRUE,
					'showRemovedLocalizationRecords' => FALSE,
					'showAllLocalizationLink' => TRUE,
				],
				'maxitems' => 99999,
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
				'default' => '#143D59',
				'placeholder' => '#143D59',
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
				'default' => '#2E6B96',
				'placeholder' => '#2E6B96',
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
		'iframe_html' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_html',
			'config' => [
				'type' => 'text',
				'renderType' => 't3editor',
				'format' => 'html',
				'eval' => 'trim'
			],
		],
		'iframe_overwritten' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_overwritten',
			'config' => [
				'type' => 'check',
				'default' => '0',
			],
		],
		'iframe_selection' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_selection',
			'onChange' => 'reload',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'minitems' => 1,
				'items' => [
					['LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_selection.0', 0],
				],
			],
		],
		'iframe_replacement_html' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_replacement_html',
			'config' => [
				'type' => 'text',
				'renderType' => 't3editor',
				'format' => 'html',
				'eval' => 'trim'
			],
		],
		'iframe_replacement_overwritten' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_replacement_overwritten',
			'config' => [
				'type' => 'check',
				'default' => '0',
			],
		],
		'iframe_replacement_selection' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_replacement_selection',
			'onChange' => 'reload',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'minitems' => 1,
				'items' => [
					['LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_replacement_selection.0', 0],
				],
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
		'session_only_essential_cookies' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.session_only_essential_cookies',
			'config' => [
				'type' => 'check',
				'default' => '0',
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
				'eval' => 'trim',
				'fieldWizard' => [
					'templatePreviewLinkWizard' => [
						'renderType' => 'templatePreviewLinkWizard',
					],
				],
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
			'onChange' => 'reload',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'minitems' => 1,
				'default' => 0,
				'items' => [
					['LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.template_selection.0', 0],
					['LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.template_selection.1', 1],
				],
			],
		],
		'disable_powered_by' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.disable_powered_by',
			'config' => [
				'type' => 'check',
				'default' => '0',
			],
		],
		'set_cookie_for_domain' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.set_cookie_for_domain',
			'description' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.set_cookie_for_domain.bad_idea',
			'config' => [
				'type' => 'input',
				'default' => '',
				'eval' => 'trim'
			],
		],
		'banner_enable' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_enable',
			'config' => [
				'type' => 'check',
				'default' => '0',
			],
		],
		'banner_html' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_html',
			'config' => [
				'type' => 'text',
				'renderType' => 't3editor',
				'format' => 'html',
				'eval' => 'trim',
				'fieldWizard' => [
					'templatePreviewLinkWizard' => [
						'renderType' => 'templatePreviewLinkWizard'
					],
				],
			],
		],
		'banner_overwritten' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_overwritten',
			'config' => [
				'type' => 'check',
				'default' => '0',
			],
		],
		'banner_show_settings_button' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_show_settings_button',
			'config' => [
				'type' => 'check',
				'default' => '1',
			],
		],
		'banner_position' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_position',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'minitems' => 1,
				'items' => [
					['LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_position.0', 0],
					['LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_position.1', 1],
				],
			],
		],
		'banner_selection' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_selection',
			'onChange' => 'reload',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'minitems' => 1,
				'items' => [
					['LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_selection.0', 0],
				],
			],
		],
		'banner_color_box' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_color_box',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#DDDDDD',
				'placeholder' => '#DDDDDD',
				'eval' => 'trim, required'
			],
		],
		'banner_color_text' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_color_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#373737',
				'placeholder' => '#373737',
				'eval' => 'trim, required'
			],
		],
		'banner_color_link_text' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_color_link_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#373737',
				'placeholder' => '#373737',
				'eval' => 'trim, required'
			],
		],
		'banner_color_button_settings' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_color_button_settings',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#A5A5A5',
				'placeholder' => '#A5A5A5',
				'eval' => 'trim, required'
			],
		],
		'banner_color_button_settings_hover' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_color_button_settings_hover',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#D7D7D7',
				'placeholder' => '#D7D7D7',
				'eval' => 'trim, required'
			],
		],
		'banner_color_button_settings_text' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_color_button_settings_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'placeholder' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
		'banner_color_button_accept' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_color_button_accept',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#143D59',
				'placeholder' => '#143D59',
				'eval' => 'trim, required'
			],
		],
		'banner_color_button_accept_hover' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_color_button_accept_hover',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#2E6B96',
				'placeholder' => '#2E6B96',
				'eval' => 'trim, required'
			],
		],
		'banner_color_button_accept_text' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_color_button_accept_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'placeholder' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
		'banner_button_accept_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_button_accept_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Akzeptieren',
				'placeholder' => 'Akzeptieren',
				'eval' => 'trim, required'
			],
		],
		'banner_button_settings_text' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_button_settings_text',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'default' => 'Einstellungen',
				'placeholder' => 'Einstellungen',
				'eval' => 'trim, required'
			],
		],
		'banner_description' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.banner_description',
			'config' => [
				'type' => 'text',
				'default' => 'Auf unserer Webseite werden Cookies verwendet. Einige davon werden zwingend benötigt, während es uns andere ermöglichen, Ihre Nutzererfahrung auf unserer Webseite zu verbessern.',
				'placeholder' => 'Auf unserer Webseite werden Cookies verwendet. Einige davon werden zwingend benötigt, während es uns andere ermöglichen, Ihre Nutzererfahrung auf unserer Webseite zu verbessern.',
				'eval' => 'trim'
			],
		],
		'show_button_close' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.show_button_close',
			'config' => [
				'type' => 'check',
				'default' => '0',
			],
		],
		'activate_testing_mode' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.activate_testing_mode',
			'config' => [
				'type' => 'check',
				'default' => '0',
			],
		],
		'color_full_box' => [
			'displayCond' => 'FIELD:template_selection:=:1',
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_full_box',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#143D59',
				'placeholder' => '#143D59',
				'eval' => 'trim, required'
			],
		],
		'color_full_headline' => [
			'displayCond' => 'FIELD:template_selection:=:1',
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_full_headline',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'placeholder' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
		'color_full_text' => [
			'displayCond' => 'FIELD:template_selection:=:1',
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_full_text',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#FFFFFF',
				'placeholder' => '#FFFFFF',
				'eval' => 'trim, required'
			],
		],
		'color_full_button_close' => [
			'displayCond' => 'FIELD:template_selection:=:1',
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_close',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#143D59',
				'placeholder' => '#143D59',
				'eval' => 'trim, required'
			],
		],
		'color_full_button_close_hover' => [
			'displayCond' => 'FIELD:template_selection:=:1',
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.color_button_close_hover',
			'config' => [
				'type' => 'input',
				'renderType' => 'colorpicker',
				'default' => '#143D59',
				'placeholder' => '#143D59',
				'eval' => 'trim, required'
			],
		],
		'color_full_button_close_text' => [
			'displayCond' => 'FIELD:template_selection:=:1',
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
		'iframe_whitelist_overwritten' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_overwritten',
			'config' => [
				'type' => 'check',
				'default' => '0',
			],
		],
		'iframe_whitelist_selection' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_whitelist_selection',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'minitems' => 1,
				'items' => [
					['LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_whitelist_selection.0', 0],
				],
			],
		],
		'iframe_whitelist_regex' => [
			'exclude' => TRUE,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:sg_cookie_optin/Resources/Private/Language/locallang_db.xlf:tx_sgcookieoptin_domain_model_optin.iframe_whitelist_regex',
			'config' => [
				'type' => 'text',
				'renderType' => 't3editor',
				'eval' => 'trim',
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
		$configuration['columns']['color_confirmation_background']['config']['renderType'],
		$configuration['columns']['color_confirmation_text']['config']['renderType'],
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
		$configuration['columns']['color_table_header']['config']['renderType'],
		$configuration['columns']['color_table_header_text']['config']['renderType'],
		$configuration['columns']['color_Table_data_text']['config']['renderType'],
		$configuration['columns']['color_full_box']['config']['renderType'],
		$configuration['columns']['color_full_headline']['config']['renderType'],
		$configuration['columns']['color_full_text']['config']['renderType'],
		$configuration['columns']['color_full_button_close']['config']['renderType'],
		$configuration['columns']['color_full_button_close_hover']['config']['renderType'],
		$configuration['columns']['color_full_button_close_text']['config']['renderType'],
		$configuration['columns']['iframe_color_consent_box_background']['config']['renderType'],
		$configuration['columns']['iframe_color_button_load_one']['config']['renderType'],
		$configuration['columns']['iframe_color_button_load_one_hover']['config']['renderType'],
		$configuration['columns']['iframe_color_button_load_one_text']['config']['renderType'],
		$configuration['columns']['iframe_color_open_settings']['config']['renderType'],
		$configuration['columns']['banner_color_box']['config']['renderType'],
		$configuration['columns']['banner_color_text']['config']['renderType'],
		$configuration['columns']['banner_color_link_text']['config']['renderType'],
		$configuration['columns']['banner_color_button_settings']['config']['renderType'],
		$configuration['columns']['banner_color_button_settings_hover']['config']['renderType'],
		$configuration['columns']['banner_color_button_settings_text']['config']['renderType'],
		$configuration['columns']['banner_color_button_accept']['config']['renderType'],
		$configuration['columns']['banner_color_button_accept_hover']['config']['renderType'],
		$configuration['columns']['banner_color_button_accept_text']['config']['renderType']
	);
}

if (version_compare(\TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version(), '10.3.0', '<')) {
	$configuration['interface']['showRecordFieldList'] = 'sys_language_uid, l10n_parent, l10n_diffsource, header,'
		. 'description, cookie_lifetime, minify_generated_data, navigation, accept_all_text, accept_specific_text,'
		. 'accept_essential_text, groups, template_html, template_overwritten, template_selection, color_text,'
		. 'color_box, color_headline, color_checkbox, color_checkbox_required, color_button_all, color_button_all_text,'
		. 'color_button_specific, color_button_specific_text, color_button_essential, color_button_essential_text,'
		. 'color_list, color_list_text, essential_title, essential_description, essential_scripts, essential_cookies,'
		. 'extend_box_link_text, extend_box_link_text_close, extend_table_link_text, extend_table_link_text_close,'
		. 'color_button_all_hover, color_button_specific_hover, color_button_essential_hover, color_table,'
		. 'color_table_header_text, color_Table_data_text, color_button_close, color_button_close_hover,'
		. 'color_button_close_text, cookie_name_text, cookie_provider_text, cookie_purpose_text, cookie_lifetime_text,'
		. 'iframe_enabled, iframe_title, iframe_description, iframe_cookies, iframe_button_allow_all_text,'
		. 'iframe_button_allow_one_text, iframe_button_load_one_text, iframe_open_settings_text,'
		. 'iframe_color_consent_box_background, iframe_color_button_load_one, iframe_color_button_load_one_hover,'
		. 'iframe_color_button_load_one_text, iframe_color_open_settings, iframe_html, iframe_overwritten,'
		. 'iframe_selection, iframe_replacement_html, iframe_replacement_overwritten, iframe_replacement_selection,'
		. 'banner_enable, banner_position, banner_overwritten, banner_html, banner_selection,'
		. 'banner_show_settings_button, banner_color_box, banner_color_text, banner_color_button_settings,'
		. 'banner_color_button_settings_hover, banner_color_button_settings_text, banner_color_button_accept,'
		. 'banner_color_button_accept_hover, banner_color_button_accept_text, banner_color_link_text,'
		. 'banner_button_accept_text, banner_button_settings_text, banner_description, show_button_close,'
		. 'activate_testing_mode, color_full_box, color_full_headline, color_full_text, color_full_button_close,'
		. 'color_full_button_close_hover, color_full_button_close_text, color_table_header, save_confirmation_text,'
		. 'color_confirmation_background, color_confirmation_text, session_only_essential_cookies, iframe_whitelist, iframe_whitelist_overwritten, iframe_whitelist_selection, iframe_whitelist_regex, set_cookie_for_domain, disable_powered_by';
}

return $configuration;
