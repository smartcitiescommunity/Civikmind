DROP TABLE IF EXISTS `glpi_plugin_manageentity_contracts`;
CREATE TABLE `glpi_plugin_manageentity_contracts` (
`ID` int(11) NOT NULL auto_increment,
`FK_contracts` int(11) NOT NULL default '0',
`FK_entity` int(11) NOT NULL default '0',
PRIMARY KEY  (`ID`),
UNIQUE KEY `FK_contracts` (`FK_contracts`,`FK_entity`),
KEY `FK_contracts_2` (`FK_contracts`),
KEY `FK_entity` (`FK_entity`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_manageentity_documents`;
CREATE TABLE `glpi_plugin_manageentity_documents` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_documents` int(11) NOT NULL default '0',
	`FK_entity` int(11) NOT NULL default '0',
	PRIMARY KEY  (`ID`),
	UNIQUE KEY `FK_documents` (`FK_documents`,`FK_entity`),
	KEY `FK_documents_2` (`FK_documents`),
	KEY `FK_entity` (`FK_entity`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_manageentity_contacts`;
CREATE TABLE `glpi_plugin_manageentity_contacts` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_contacts` int(11) NOT NULL default '0',
	`FK_entity` int(11) NOT NULL default '0',
	PRIMARY KEY  (`ID`),
	UNIQUE KEY `FK_contacts` (`FK_contacts`,`FK_entity`),
	KEY `FK_contacts_2` (`FK_contacts`),
	KEY `FK_entity` (`FK_entity`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_manageentity_profiles`;
CREATE TABLE `glpi_plugin_manageentity_profiles` (
	`ID` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`interface` varchar(50) collate utf8_unicode_ci NOT NULL default 'manageentity',
	`is_default` smallint(6) NOT NULL default '0',
	`manageentity` char(1) default NULL,
	PRIMARY KEY  (`ID`),
	KEY `interface` (`interface`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_manageentity_preference`;
CREATE TABLE `glpi_plugin_manageentity_preference` (
	`ID` int(11) NOT NULL auto_increment,
	`user_id` int(11) NOT NULL,
	`show` varchar(255) NOT NULL,
	PRIMARY KEY  (`ID`)
) ENGINE=MyISAM;