DROP TABLE IF EXISTS `glpi_plugin_manageentity_contracts`;
CREATE TABLE `glpi_plugin_manageentity_contracts` (
`ID` int(11) NOT NULL auto_increment,
`FK_contracts` int(11) NOT NULL default '0',
`FK_entities` int(11) NOT NULL default '0',
`isdefault` int(11) NOT NULL default '0',
PRIMARY KEY  (`ID`),
UNIQUE KEY `FK_contracts` (`FK_contracts`,`FK_entities`),
KEY `FK_contracts_2` (`FK_contracts`),
KEY `FK_entities` (`FK_entities`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_manageentity_documents`;
CREATE TABLE `glpi_plugin_manageentity_documents` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_documents` int(11) NOT NULL default '0',
	`FK_entities` int(11) NOT NULL default '0',
	PRIMARY KEY  (`ID`),
	UNIQUE KEY `FK_documents` (`FK_documents`,`FK_entities`),
	KEY `FK_documents_2` (`FK_documents`),
	KEY `FK_entities` (`FK_entities`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_manageentity_contacts`;
CREATE TABLE `glpi_plugin_manageentity_contacts` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_contacts` int(11) NOT NULL default '0',
	`FK_entities` int(11) NOT NULL default '0',
	`isdefault` int(11) NOT NULL default '0',
	PRIMARY KEY  (`ID`),
	UNIQUE KEY `FK_contacts` (`FK_contacts`,`FK_entities`),
	KEY `FK_contacts_2` (`FK_contacts`),
	KEY `FK_entities` (`FK_entities`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_manageentity_profiles`;
CREATE TABLE `glpi_plugin_manageentity_profiles` (
	`ID` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`manageentity` char(1) default NULL,
	`cri` char(1) default NULL,
	PRIMARY KEY  (`ID`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_manageentity_preference`;
CREATE TABLE `glpi_plugin_manageentity_preference` (
	`ID` int(11) NOT NULL auto_increment,
	`user_id` int(11) NOT NULL,
	`show` varchar(255) NOT NULL,
	PRIMARY KEY  (`ID`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `glpi_plugin_manageentity_config`;
CREATE TABLE `glpi_plugin_manageentity_config` (
	`ID` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`backup` INT( 11 ) NOT NULL ,
	`rubrique` INT( 11 ) NOT NULL,
	`hourbyday` INT( 11 ) NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_dropdown_plugin_manageentity_critype`;
CREATE TABLE `glpi_dropdown_plugin_manageentity_critype` (
	`ID` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`comments` text,
	PRIMARY KEY  (`ID`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_manageentity_criprice`;
CREATE TABLE `glpi_plugin_manageentity_criprice` (
	`ID` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`FK_entities` int(11) NOT NULL default '0',
	`FK_typecri` INT( 11 ) NOT NULL ,
	`price` decimal(20,4) NOT NULL default '0.0000'
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_manageentity_dayforcontract`;
CREATE TABLE `glpi_plugin_manageentity_dayforcontract` (
	`ID` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`FK_entities` int(11) NOT NULL default '0',
	`FK_typecri` INT( 11 ) NOT NULL ,
	`FK_contracts` INT( 11 ) NOT NULL ,
	`nbday` decimal(20,2) default '0.00' 
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_manageentity_critechnicians`;
CREATE TABLE `glpi_plugin_manageentity_critechnicians` (
	`ID` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`FK_ticket` INT( 11 ) NOT NULL ,
	`FK_users` INT( 11 ) NOT NULL 
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_manageentity_cridetails`;
CREATE TABLE `glpi_plugin_manageentity_cridetails` (
	`ID` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`FK_entities` int(11) NOT NULL default '0',
	`date` date default NULL,
	`FK_doc` INT( 11 ) NOT NULL ,
	`type_cri` INT( 11 ) NOT NULL ,
	`withcontract` INT( 11 ) NOT NULL ,
	`FK_contracts` INT( 11 ) NOT NULL,
	`realtime` decimal(20,2) default '0.00',
	`technicians` varchar(255) collate utf8_unicode_ci NOT NULL default ''
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_manageentity_config` (`ID`,`backup`,`rubrique`,`hourbyday`) VALUES ('1', '0','-1','8');