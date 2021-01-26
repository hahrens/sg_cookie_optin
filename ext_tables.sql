--
-- Table structure for table 'tx_sgcookieoptin_domain_model_optin'
--
CREATE TABLE tx_sgcookieoptin_domain_model_optin (
	uid int(11) NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,

	-- general columns
	header varchar(255) DEFAULT 'Datenschutzeinstellungen' NOT NULL,
	description text NOT NULL,
	navigation varchar(255) DEFAULT '' NOT NULL,
	groups int(11) DEFAULT '0' NOT NULL,

	-- general texts
	accept_all_text varchar(255) DEFAULT 'Alle akzeptieren' NOT NULL,
	accept_specific_text varchar(255) DEFAULT 'Speichern & schließen' NOT NULL,
	accept_essential_text varchar(255) DEFAULT 'Nur essentielle Cookies akzeptieren' NOT NULL,
	extend_box_link_text varchar(255) DEFAULT 'Weitere Informationen anzeigen' NOT NULL,
	extend_box_link_text_close varchar(255) DEFAULT 'Weitere Informationen verstecken' NOT NULL,
	extend_table_link_text varchar(255) DEFAULT 'Cookie-Informationen anzeigen' NOT NULL,
	extend_table_link_text_close varchar(255) DEFAULT 'Cookie-Informationen verstecken' NOT NULL,
	cookie_name_text varchar(255) DEFAULT 'Name' NOT NULL,
	cookie_provider_text varchar(255) DEFAULT 'Anbieter' NOT NULL,
	cookie_purpose_text varchar(255) DEFAULT 'Zweck' NOT NULL,
	cookie_lifetime_text varchar(255) DEFAULT 'Laufzeit' NOT NULL,
	save_confirmation_text varchar(255) DEFAULT 'Cookie-Einstellungen erfolgreich gespeichert' NOT NULL,

	-- template
	template_html text NOT NULL,
	template_overwritten tinyint(4) unsigned DEFAULT '0' NOT NULL,
	template_selection int(11) DEFAULT '0' NOT NULL,
	disable_powered_by tinyint(4) unsigned DEFAULT '0' NOT NULL,


	-- banner
	banner_enable tinyint(4) unsigned DEFAULT '0' NOT NULL,
	banner_html text NOT NULL,
	banner_overwritten tinyint(4) unsigned DEFAULT '0' NOT NULL,
	banner_show_settings_button tinyint(4) unsigned DEFAULT '1' NOT NULL,
	banner_position int(11) DEFAULT '0' NOT NULL,
	banner_selection int(11) DEFAULT '0' NOT NULL,
	banner_color_box varchar(10) DEFAULT '#DDDDDD' NOT NULL,
	banner_color_text varchar(10) DEFAULT '#373737' NOT NULL,
	banner_color_link_text varchar(10) DEFAULT '#373737' NOT NULL,
	banner_color_button_settings varchar(10) DEFAULT '#888888' NOT NULL,
	banner_color_button_settings_hover varchar(10) DEFAULT '#D7D7D7' NOT NULL,
	banner_color_button_settings_text varchar(10) DEFAULT '#FFFFFF' NOT NULL,
	banner_color_button_accept varchar(10) DEFAULT '#143D59' NOT NULL,
	banner_color_button_accept_hover varchar(10) DEFAULT '#2E6B96' NOT NULL,
	banner_color_button_accept_text varchar(10) DEFAULT '#FFFFFF' NOT NULL,
	banner_button_accept_text varchar(255) DEFAULT 'Akzeptieren' NOT NULL,
	banner_button_settings_text varchar(255) DEFAULT 'Einstellungen' NOT NULL,
	banner_description text NOT NULL,

	-- template colors
	color_box varchar(10) DEFAULT '#FFFFFF' NOT NULL,
	color_headline varchar(10) DEFAULT '#373737' NOT NULL,
	color_text varchar(10) DEFAULT '#373737' NOT NULL,
	color_confirmation_background varchar(10) DEFAULT '#C9FFC9' NOT NULL,
	color_confirmation_text varchar(10) DEFAULT '#208A20' NOT NULL,
	color_checkbox varchar(10) DEFAULT '#143D59' NOT NULL,
	color_checkbox_required varchar(10) DEFAULT '#888888' NOT NULL,
	color_button_all varchar(10) DEFAULT '#143D59' NOT NULL,
	color_button_all_hover varchar(10) DEFAULT '#2E6B96' NOT NULL,
	color_button_all_text varchar(10) DEFAULT '#FFFFFF' NOT NULL,
	color_button_specific varchar(10) DEFAULT '#888888' NOT NULL,
	color_button_specific_hover varchar(10) DEFAULT '#D7D7D7' NOT NULL,
	color_button_specific_text varchar(10) DEFAULT '#FFFFFF' NOT NULL,
	color_button_essential varchar(10) DEFAULT '#888888' NOT NULL,
	color_button_essential_hover varchar(10) DEFAULT '#D7D7D7' NOT NULL,
	color_button_essential_text varchar(10) DEFAULT '#FFFFFF' NOT NULL,
	color_list varchar(10) DEFAULT '#888888' NOT NULL,
	color_list_text varchar(10) DEFAULT '#FFFFFF' NOT NULL,
	color_table varchar(10) DEFAULT '#FFFFFF' NOT NULL,
	color_Table_data_text varchar(10) DEFAULT '#373737' NOT NULL,
	color_table_header varchar(10) DEFAULT '#F3F3F3' NOT NULL,
	color_table_header_text varchar(10) DEFAULT '#373737' NOT NULL,
	color_button_close varchar(10) DEFAULT '#FFFFFF' NOT NULL,
	color_button_close_hover varchar(10) DEFAULT '#FFFFFF' NOT NULL,
	color_button_close_text varchar(10) DEFAULT '#373737' NOT NULL,

	-- Template Full
	color_full_box varchar(10) DEFAULT '#143D59' NOT NULL,
	color_full_headline varchar(10) DEFAULT '#FFFFFF' NOT NULL,
	color_full_text varchar(10) DEFAULT '#FFFFFF' NOT NULL,
	color_full_button_close varchar(10) DEFAULT '#143D59' NOT NULL,
	color_full_button_close_hover varchar(10) DEFAULT '#143D59' NOT NULL,
	color_full_button_close_text varchar(10) DEFAULT '#FFFFFF' NOT NULL,

	-- Essential group specific columns
	essential_title varchar(255) DEFAULT 'Essentiell' NOT NULL,
	essential_description text NOT NULL,
	essential_scripts int(11) DEFAULT '0' NOT NULL,
	essential_cookies int(11) DEFAULT '0' NOT NULL,

	-- IFrame group specific columns
	iframe_enabled tinyint(4) unsigned DEFAULT '0' NOT NULL,
	iframe_title varchar(255) DEFAULT 'Externe Inhalte' NOT NULL,
	iframe_description text NOT NULL,
	iframe_cookies int(11) DEFAULT '0' NOT NULL,

	iframe_html text NOT NULL,
	iframe_overwritten tinyint(4) unsigned DEFAULT '0' NOT NULL,
	iframe_selection int(11) DEFAULT '0' NOT NULL,

	iframe_replacement_html text NOT NULL,
	iframe_replacement_overwritten tinyint(4) unsigned DEFAULT '0' NOT NULL,
	iframe_replacement_selection int(11) DEFAULT '0' NOT NULL,

	iframe_whitelist_regex text NOT NULL,
	iframe_whitelist_overwritten tinyint(4) unsigned DEFAULT '0' NOT NULL,
	iframe_whitelist_selection int(11) DEFAULT '0' NOT NULL,

	iframe_button_allow_all_text varchar(255) DEFAULT 'Alle externen Inhalte erlauben' NOT NULL,
	iframe_button_allow_one_text varchar(255) DEFAULT 'Einmalig erlauben' NOT NULL,
	iframe_button_load_one_text varchar(255) DEFAULT 'Externen Inhalt laden' NOT NULL,
	iframe_open_settings_text varchar(255) DEFAULT 'Einstellungen anzeigen' NOT NULL,
	iframe_button_load_one_description text NOT NULL,

	iframe_color_consent_box_background varchar(10) DEFAULT '#D6D6D6' NOT NULL,
	iframe_color_button_load_one varchar(10) DEFAULT '#143D59' NOT NULL,
	iframe_color_button_load_one_hover varchar(10) DEFAULT '#2E6B96' NOT NULL,
	iframe_color_button_load_one_text varchar(10) DEFAULT '#FFFFFF' NOT NULL,
	iframe_color_open_settings varchar(10) DEFAULT '#373737' NOT NULL,

	-- Settings
	cookie_lifetime int(11) DEFAULT '365' NOT NULL,
	session_only_essential_cookies tinyint(4) unsigned DEFAULT '0' NOT NULL,
	minify_generated_data tinyint(4) unsigned DEFAULT '1' NOT NULL,
	show_button_close tinyint(4) unsigned DEFAULT '0' NOT NULL,
	activate_testing_mode tinyint(4) unsigned DEFAULT '0' NOT NULL,
	banner_show_again_interval int(11) unsigned DEFAULT '14' NOT NULL,
	disable_for_this_language tinyint(4) unsigned DEFAULT '0' NOT NULL,
	set_cookie_for_domain varchar(255) DEFAULT '' NOT NULL,
	cookiebanner_whitelist_regex TEXT NOT NULL,
	version int(11) unsigned DEFAULT '1' NOT NULL,
	update_version_checkbox tinyint(4) unsigned DEFAULT '0' NOT NULL,

	-- TYPO3 related columns
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY language (l10n_parent,sys_language_uid)
);

--
-- Table structure for table 'tx_sgcookieoptin_domain_model_group'
--
CREATE TABLE tx_sgcookieoptin_domain_model_group (
	uid int(11) NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,

	title varchar(255) DEFAULT '' NOT NULL,
	group_name varchar(255) DEFAULT '' NOT NULL,
	description text NOT NULL,
	parent_optin int(11) DEFAULT '0' NOT NULL,
	scripts int(11) DEFAULT '0' NOT NULL,
	cookies int(11) DEFAULT '0' NOT NULL,

	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY parent_optin (parent_optin),
	KEY language (l10n_parent,sys_language_uid)
);

--
-- Table structure for table 'tx_sgcookieoptin_domain_model_script'
--
CREATE TABLE tx_sgcookieoptin_domain_model_script (
	uid int(11) NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,

	title varchar(255) DEFAULT '' NOT NULL,
	script text NOT NULL,
	html text NOT NULL,
	parent_group int(11) DEFAULT '0' NOT NULL,
	parent_optin int(11) DEFAULT '0' NOT NULL,


	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY parent_group (parent_group),
	KEY language (l10n_parent,sys_language_uid)
);

--
-- Table structure for table 'tx_sgcookieoptin_domain_model_cookie'
--
CREATE TABLE tx_sgcookieoptin_domain_model_cookie (
	uid int(11) NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,

	name varchar(255) DEFAULT '' NOT NULL,
	provider varchar(255) DEFAULT '' NOT NULL,
	purpose text NOT NULL,
	lifetime varchar(255) DEFAULT '' NOT NULL,
	parent_group int(11) DEFAULT '0' NOT NULL,
	parent_optin int(11) DEFAULT '0' NOT NULL,
	parent_iframe int(11) DEFAULT '0' NOT NULL,


	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY parent_group (parent_group),
	KEY language (l10n_parent,sys_language_uid)
);

--
-- Table structure for table 'tx_sgcookieoptin_domain_model_user_preference'
--
CREATE TABLE tx_sgcookieoptin_domain_model_user_preference (
	uid int(11) NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,

	user_hash VARCHAR(255) NOT NULL,
	version int(11) unsigned NOT NULL,
	item_identifier varchar(255) NOT NULL,
	item_type int(11) unsigned NOT NULL,
	is_accepted tinyint(4) unsigned NOT NULL,
	is_all tinyint(4) unsigned NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY user_uid (user_uid),
	KEY version (version),
	KEY crdate (crdate),
	KEY is_accepted (is_accepted),
	KEY item_type (item_type),
	KEY item_identifier (item_identifier),
	KEY is_all (is_all)
);
