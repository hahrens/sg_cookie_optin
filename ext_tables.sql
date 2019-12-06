#
# Table structure for table 'tx_sgcookieoptin_domain_model_optin'
#
CREATE TABLE tx_sgcookieoptin_domain_model_optin (
	uid int(11) NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,

	# general columns
	header varchar(255) DEFAULT 'Datenschutzeinstellungen' NOT NULL,
	description text NOT NULL,
	cookie_lifetime int(11) DEFAULT '365' NOT NULL,
	navigation varchar(255) DEFAULT '' NOT NULL,
	groups int(11) DEFAULT '0' NOT NULL,

	# general texts
	accept_all_text varchar(255) DEFAULT 'Alle akzeptieren' NOT NULL,
	accept_specific_text varchar(255) DEFAULT 'Speichern & schließen' NOT NULL,
	accept_essential_text varchar(255) DEFAULT 'Nur essentielle Cookies akzeptieren' NOT NULL,
	extend_box_link_text varchar(255) DEFAULT 'Weitere Informationen öffnen' NOT NULL,
	extend_box_link_text_close varchar(255) DEFAULT 'Weitere Informationen schließen' NOT NULL,
	extend_table_link_text varchar(255) DEFAULT 'Cookie-Informationen öffnen' NOT NULL,
	extend_table_link_text_close varchar(255) DEFAULT 'Cookie-Informationen schließen' NOT NULL,
	cookie_name_text varchar(255) DEFAULT 'Name' NOT NULL,
	cookie_provider_text varchar(255) DEFAULT 'Anbieter' NOT NULL,
	cookie_purpose_text varchar(255) DEFAULT 'Zweck' NOT NULL,
	cookie_lifetime_text varchar(255) DEFAULT 'Laufzeit' NOT NULL,

	# general colors
	color_box varchar(255) DEFAULT '#FFFFFF' NOT NULL,
	color_headline varchar(255) DEFAULT '#373737' NOT NULL,
	color_text varchar(255) DEFAULT '#373737' NOT NULL,
	color_checkbox varchar(255) DEFAULT '#7B9B2C' NOT NULL,
	color_checkbox_required varchar(255) DEFAULT '#A5A5A5' NOT NULL,
	color_button_all varchar(255) DEFAULT '#7B9B2C' NOT NULL,
	color_button_all_hover varchar(255) DEFAULT '#8FAF2D' NOT NULL,
	color_button_all_text varchar(255) DEFAULT '#FFFFFF' NOT NULL,
	color_button_specific varchar(255) DEFAULT '#A5A5A5' NOT NULL,
	color_button_specific_hover varchar(255) DEFAULT '#D7D7D7' NOT NULL,
	color_button_specific_text varchar(255) DEFAULT '#FFFFFF' NOT NULL,
	color_button_essential varchar(255) DEFAULT '#A5A5A5' NOT NULL,
	color_button_essential_hover varchar(255) DEFAULT '#D7D7D7' NOT NULL,
	color_button_essential_text varchar(255) DEFAULT '#FFFFFF' NOT NULL,
	color_list varchar(255) DEFAULT '#A5A5A5' NOT NULL,
	color_list_text varchar(255) DEFAULT '#FFFFFF' NOT NULL,
	color_table varchar(255) DEFAULT '#FFFFFF' NOT NULL,
	color_table_header_text varchar(255) DEFAULT '#373737' NOT NULL,
	color_Table_data_text varchar(255) DEFAULT '#373737' NOT NULL,
	color_button_close varchar(255) DEFAULT '#FFFFFF' NOT NULL,
	color_button_close_hover varchar(255) DEFAULT '#FFFFFF' NOT NULL,
	color_button_close_text varchar(255) DEFAULT '#373737' NOT NULL,

	# Essential group specific columns
	essential_title varchar(255) DEFAULT 'Essentiell' NOT NULL,
	essential_description text NOT NULL,
	essential_scripts int(11) DEFAULT '0' NOT NULL,
	essential_cookies int(11) DEFAULT '0' NOT NULL,

	# IFrame group specific columns
	iframe_enabled tinyint(4) unsigned DEFAULT '0' NOT NULL,
	iframe_title varchar(255) DEFAULT 'Externe Inhalte' NOT NULL,
	iframe_description text NOT NULL,

	iframe_button_allow_all_text varchar(255) DEFAULT 'Alle externen Inhalte erlauben' NOT NULL,
	iframe_button_allow_one_text varchar(255) DEFAULT 'Einmalig erlauben' NOT NULL,
	iframe_button_load_one_text varchar(255) DEFAULT 'Externen Inhalt laden' NOT NULL,
	iframe_open_settings_text varchar(255) DEFAULT 'Einstellungen öffnen' NOT NULL,

	iframe_color_consent_box_background varchar(255) DEFAULT '#D6D6D6' NOT NULL,
	iframe_color_button_load_one varchar(255) DEFAULT '#7B9B2C' NOT NULL,
	iframe_color_button_load_one_hover varchar(255) DEFAULT '#8FAF2D' NOT NULL,
	iframe_color_button_load_one_text varchar(255) DEFAULT '#FFFFFF' NOT NULL,
	iframe_color_open_settings varchar(255) DEFAULT '#373737' NOT NULL,

	# TYPO3 related columns
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

#
# Table structure for table 'tx_sgcookieoptin_domain_model_group'
#
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

#
# Table structure for table 'tx_sgcookieoptin_domain_model_script'
#
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

#
# Table structure for table 'tx_sgcookieoptin_domain_model_cookie'
#
CREATE TABLE tx_sgcookieoptin_domain_model_cookie (
	uid int(11) NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,

	name varchar(255) DEFAULT '' NOT NULL,
	provider varchar(255) DEFAULT '' NOT NULL,
	purpose text NOT NULL,
	lifetime varchar(255) DEFAULT '' NOT NULL,
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
