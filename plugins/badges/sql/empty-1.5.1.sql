DROP TABLE IF EXISTS `glpi_plugin_badges`;
CREATE TABLE `glpi_plugin_badges` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_entities` int(11) NOT NULL default '0',
	`name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`serial` varchar(255) collate utf8_unicode_ci default NULL,
	`type` tinyint(4) NOT NULL default '1',
	`location` tinyint(4) NOT NULL default '1',
	`date_affect` DATE NULL default NULL,
	`date_expiration` DATE NULL default NULL,
	`state` tinyint(4) NOT NULL default '0',
	`FK_users` int(4),
	`helpdesk_visible` int(11) NOT NULL default '1',
	`date_mod` datetime default NULL,
	`comments` text,
	`notes` LONGTEXT,
	`deleted` smallint(6) NOT NULL default '0',
	PRIMARY KEY  (`ID`),
	KEY `name` (`name`),
	KEY `deleted` (`deleted`),
	KEY `FK_entities` (`FK_entities`),
	KEY `date_expiration` (`date_expiration`),
	KEY `state` (`state`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
	
DROP TABLE IF EXISTS `glpi_dropdown_plugin_badges_type`;
	CREATE TABLE `glpi_dropdown_plugin_badges_type` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_entities` int(11) NOT NULL default '0',
	`name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`comments` text,
	PRIMARY KEY  (`ID`),
	KEY `name` (`name`),
	KEY `FK_entities` (`FK_entities`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
	
DROP TABLE IF EXISTS `glpi_plugin_badges_profiles`;
CREATE TABLE `glpi_plugin_badges_profiles` (
	`ID` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`badges` char(1) default NULL,
	`open_ticket` char(1) default NULL,
	PRIMARY KEY  (`ID`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_badges_default`;
CREATE TABLE `glpi_plugin_badges_default` (
	`ID` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`status` INT( 11 ) NOT NULL,
	KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_badges_config`;
	CREATE TABLE `glpi_plugin_badges_config` (
	`ID` int(11) NOT NULL auto_increment,
	`delay` varchar(50) collate utf8_unicode_ci NOT NULL default '30',
	PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
	
INSERT INTO `glpi_plugin_badges_config` VALUES (1, '30');
	
DROP TABLE IF EXISTS `glpi_plugin_badges_mailing`;
CREATE TABLE `glpi_plugin_badges_mailing` (
	`ID` int(11) NOT NULL auto_increment,
	`type` varchar(255) collate utf8_unicode_ci collate utf8_unicode_ci default NULL,
	`FK_item` int(11) NOT NULL default '0',
	`item_type` int(11) NOT NULL default '0',
	PRIMARY KEY  (`ID`),
	UNIQUE KEY `mailings` (`type`,`FK_item`,`item_type`),
	KEY `type` (`type`),
	KEY `FK_item` (`FK_item`),
	KEY `item_type` (`item_type`),
	KEY `items` (`item_type`,`FK_item`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO glpi_plugin_badges_mailing VALUES ('1','badges','1','1');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'1600','3','2','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'1600','4','3','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'1600','5','4','0');