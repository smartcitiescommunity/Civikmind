DROP TABLE IF EXISTS `glpi_plugin_resources`;
CREATE TABLE `glpi_plugin_resources` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_entities` int(11) NOT NULL default '0',
	`name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`firstname` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`type` tinyint(4) NOT NULL default '0',
	`FK_users` int(4) NOT NULL default '0',
	`recipient` int(4) NOT NULL default '0',
	`date` DATE NULL default NULL,
	`begin_date` DATE NULL default NULL,
	`end_date` DATE NULL default NULL,
	`department` tinyint(4) NOT NULL default '0',
	`location` INT( 4 ) NOT NULL,
	`leaving` smallint(6) NOT NULL default '0',
	`recipient_leaving` int(4) NOT NULL default '0',
	`helpdesk_visible` int(11) NOT NULL default '1',
	`date_mod` datetime default NULL,
	`comments` text,
	`notes` longtext,
	`is_template` smallint(6) NOT NULL default '0',
	`tplname` varchar(200) collate utf8_unicode_ci NOT NULL default '',
	`deleted` smallint(6) NOT NULL default '0',
	PRIMARY KEY  (`ID`),
	KEY `deleted` (`deleted`),
	KEY `is_template` (`is_template`),
	KEY `end_date` (`end_date`),
	KEY `leaving` (`leaving`),
	KEY `FK_users` (`FK_users`),
	KEY `recipient` (`recipient`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_needs`;
CREATE TABLE `glpi_plugin_resources_needs` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_resources` int(11) NOT NULL default '0',
	`device_type` int(11) NOT NULL default '0',
	`comments` text,
	`is_template` smallint(6) NOT NULL default '0',
	PRIMARY KEY  (`ID`),
	UNIQUE KEY `FK_resources` (`FK_resources`,`device_type`),
	KEY `FK_resources_2` (`FK_resources`),
	KEY `FK_device` (`device_type`),
	KEY `is_template` (`is_template`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_device`;
CREATE TABLE `glpi_plugin_resources_device` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_resources` int(11) NOT NULL default '0',
	`FK_device` int(11) NOT NULL default '0',
	`device_type` int(11) NOT NULL default '0',
	`comments` text,
	`is_template` smallint(6) NOT NULL default '0',
	PRIMARY KEY  (`ID`),
	UNIQUE KEY `FK_resources` (`FK_resources`,`FK_device`,`device_type`),
	KEY `FK_resources_2` (`FK_resources`),
	KEY `FK_device` (`FK_device`,`device_type`),
	KEY `is_template` (`is_template`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_employee`;
CREATE TABLE `glpi_plugin_resources_employee` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_resources` int(11) NOT NULL default '0',
	`FK_employer` int(11) NOT NULL default '0',
	`FK_client` int(11) NOT NULL default '0',
	`matricule` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
	`is_template` smallint(6) NOT NULL default '0',
	PRIMARY KEY  (`ID`),
	KEY `FK_resources` (`FK_resources`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_dropdown_plugin_resources_employer`;
CREATE TABLE `glpi_dropdown_plugin_resources_employer` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_entities` int(11) NOT NULL default '0',
	`name` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
	`comments` text,
	PRIMARY KEY  (`ID`),
	KEY `name` (`name`),
	KEY `FK_entities` (`FK_entities`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_dropdown_plugin_resources_client`;
CREATE TABLE `glpi_dropdown_plugin_resources_client` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_entities` int(11) NOT NULL default '0',
	`name` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
	`comments` text,
	PRIMARY KEY  (`ID`),
	KEY `name` (`name`),
	KEY `FK_entities` (`FK_entities`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_dropdown_plugin_resources_type`;
CREATE TABLE `glpi_dropdown_plugin_resources_type` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_entities` int(11) NOT NULL default '0',
	`name` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
	`comments` text,
	PRIMARY KEY  (`ID`),
	KEY `name` (`name`),
	KEY `FK_entities` (`FK_entities`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_dropdown_plugin_resources_department`;
CREATE TABLE `glpi_dropdown_plugin_resources_department` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_entities` int(11) NOT NULL default '0',
	`name` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
	`comments` text,
	PRIMARY KEY  (`ID`),
	KEY `name` (`name`),
	KEY `FK_entities` (`FK_entities`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_tasks`;
CREATE TABLE `glpi_plugin_resources_tasks` (
	`ID` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`FK_resources` int(11) NOT NULL default '0',
	`type_task` int(4) NOT NULL default '0',
	`assign` int(11) NOT NULL default '0',
	`assign_group` int(11) NOT NULL default '0',
	`begin_date` DATETIME NULL default NULL,
	`end_date` DATETIME NULL default NULL,
	`use_planning` smallint(6) NOT NULL default '1',
	`realtime` float DEFAULT '0' NOT NULL,
	`installed` int(11) NOT NULL default '0',
	`comments` text,
	`deleted` smallint(6) NOT NULL default '0',
	PRIMARY KEY  (`ID`),
	KEY `FK_resources` (`FK_resources`),
	KEY `end_date` (`end_date`),
	KEY `deleted` (`deleted`),
	KEY `installed` (`installed`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_dropdown_plugin_resources_tasks_type`;
CREATE TABLE `glpi_dropdown_plugin_resources_tasks_type` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_entities` int(11) NOT NULL default '0',
	`name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`comments` text,
	PRIMARY KEY  (`ID`),
	KEY `name` (`name`),
	KEY `FK_entities` (`FK_entities`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_tasks_items`;
CREATE TABLE `glpi_plugin_resources_tasks_items` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_task` int(11) NOT NULL default '0',
	`FK_device` int(11) NOT NULL default '0',
	`device_type` int(11) NOT NULL default '0',
	`is_template` smallint(6) NOT NULL default '0',
	PRIMARY KEY  (`ID`),
	KEY `FK_device` (`FK_device`,`FK_task`),
	KEY `FK_task` (`FK_task`),
	KEY `FK_device_2` (`FK_device`),
	KEY device_type (`device_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_checklists`;
CREATE TABLE `glpi_plugin_resources_checklists` (
	`ID` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`FK_entities` int(11) NOT NULL default '0',
	`FK_resources` int(11) NOT NULL default '0',
	`FK_task` int(11) NOT NULL default '0',
	`type` tinyint(4) NOT NULL default '0',
	`checklist_type` int(11) NOT NULL default '0',
	`checked` int(11) NOT NULL default '0',
	`is_default` smallint(6) NOT NULL default '0',
	`address` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`rank` smallint(6) NOT NULL default '0',
	`comments` text,
	PRIMARY KEY  (`ID`),
	KEY `name` (`name`),
	KEY `FK_resources` (`FK_resources`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_profiles`;
CREATE TABLE `glpi_plugin_resources_profiles` (
	`ID` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`resources` char(1) default NULL,
	`task` char(1) default NULL,
	`all` char(1) default NULL,
	`employer` char(1) default NULL,
	`open_ticket` char(1) default NULL,
	PRIMARY KEY  (`ID`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_mailing`;
CREATE TABLE `glpi_plugin_resources_mailing` (
	`ID` int(11) NOT NULL auto_increment,
	`type` varchar(255) collate utf8_unicode_ci default NULL,
	`FK_item` int(11) NOT NULL default '0',
	`item_type` int(11) NOT NULL default '0',
	PRIMARY KEY  (`ID`),
	UNIQUE KEY `mailings` (`type`,`FK_item`,`item_type`),
	KEY `type` (`type`),
	KEY `FK_item` (`FK_item`),
	KEY `item_type` (`item_type`),
	KEY `items` (`item_type`,`FK_item`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO glpi_plugin_resources_mailing VALUES ('1','resources','1','1');
INSERT INTO glpi_plugin_resources_mailing VALUES ('2','task','1','1');
INSERT INTO glpi_plugin_resources_mailing VALUES ('3','checklists','1','1');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4300','2','1','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4300','3','2','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4300','4','3','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4300','5','4','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4300','6','5','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4300','8','6','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4301','2','1','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4301','3','2','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4301','4','3','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4301','6','4','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4301','7','5','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4302','2','1','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4302','3','2','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4302','4','3','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4302','6','5','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4302','7','6','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4302','8','7','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4302','9','8','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4303','3','1','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4303','4','2','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4303','5','5','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4303','6','4','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4303','7','7','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4303','8','6','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4303','9','8','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4303','10','3','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4303','11','9','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'4303','12','10','0');