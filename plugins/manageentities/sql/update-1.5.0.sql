ALTER TABLE `glpi_plugin_manageentity_profiles` DROP COLUMN `interface`, DROP COLUMN `is_default`, ADD `cri` char(1) default NULL AFTER `manageentity`;

DROP TABLE IF EXISTS `glpi_plugin_manageentity_config`;
CREATE TABLE `glpi_plugin_manageentity_config` (
	`ID` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`backup` INT( 11 ) NOT NULL ,
	`rubrique` INT( 11 ) NOT NULL 
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

ALTER TABLE `glpi_plugin_manageentity_contacts` ADD `isdefault` int(11) NOT NULL default '0';
ALTER TABLE `glpi_plugin_manageentity_contracts` ADD `isdefault` int(11) NOT NULL default '0';
ALTER TABLE `glpi_plugin_manageentity_contacts` CHANGE  FK_entity `FK_entities` int(11) NOT NULL default '0';
ALTER TABLE `glpi_plugin_manageentity_contracts` CHANGE  FK_entity `FK_entities` int(11) NOT NULL default '0';
ALTER TABLE `glpi_plugin_manageentity_documents` CHANGE  FK_entity `FK_entities` int(11) NOT NULL default '0';
INSERT INTO `glpi_plugin_manageentity_config` ( `ID`, `backup` , `rubrique`) VALUES ('1', '0','-1');