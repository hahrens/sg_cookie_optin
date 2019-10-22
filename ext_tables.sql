#
# Table structure for table 'tx_sgcookieoptin_domain_model_optin'
#
CREATE TABLE tx_sgcookieoptin_domain_model_optin (
	uid int(11) NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,

	header varchar(255) DEFAULT '' NOT NULL,
	description text NOT NULL,
	navigation varchar(255) DEFAULT '' NOT NULL,
	accept_all_text varchar(255) DEFAULT '' NOT NULL,
	accept_specific_text varchar(255) DEFAULT '' NOT NULL,
	accept_essential_text varchar(255) DEFAULT '' NOT NULL,
	extend_box_link_text varchar(255) DEFAULT '' NOT NULL,
	extend_table_link_text varchar(255) DEFAULT '' NOT NULL,
	groups int(11) DEFAULT '0' NOT NULL,

	essential_title varchar(255) DEFAULT '' NOT NULL,
	essential_description text NOT NULL,
	essential_scripts int(11) DEFAULT '0' NOT NULL,
	essential_cookies int(11) DEFAULT '0' NOT NULL,

	color_box varchar(255) DEFAULT '' NOT NULL,
	color_headline varchar(255) DEFAULT '' NOT NULL,
	color_text varchar(255) DEFAULT '' NOT NULL,
	color_checkbox varchar(255) DEFAULT '' NOT NULL,
	color_checkbox_required varchar(255) DEFAULT '' NOT NULL,
	color_button_all varchar(255) DEFAULT '' NOT NULL,
	color_button_all_hover varchar(255) DEFAULT '' NOT NULL,
	color_button_all_text varchar(255) DEFAULT '' NOT NULL,
	color_button_specific varchar(255) DEFAULT '' NOT NULL,
	color_button_specific_hover varchar(255) DEFAULT '' NOT NULL,
	color_button_specific_text varchar(255) DEFAULT '' NOT NULL,
	color_button_essential varchar(255) DEFAULT '' NOT NULL,
	color_button_essential_hover varchar(255) DEFAULT '' NOT NULL,
	color_button_essential_text varchar(255) DEFAULT '' NOT NULL,
	color_list varchar(255) DEFAULT '' NOT NULL,
	color_list_text varchar(255) DEFAULT '' NOT NULL,
	color_table varchar(255) DEFAULT '' NOT NULL,
	color_table_header_text varchar(255) DEFAULT '' NOT NULL,
	color_Table_data_text varchar(255) DEFAULT '' NOT NULL,

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
