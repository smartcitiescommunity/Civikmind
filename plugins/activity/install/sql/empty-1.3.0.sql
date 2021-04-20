DROP TABLE IF EXISTS `glpi_plugin_activity`;
CREATE TABLE `glpi_plugin_activity` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_entities` int(11) NOT NULL default '0',
	`name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`begin_date` datetime NOT NULL default '0000-00-00 00:00:00',
	`end_date` datetime NOT NULL default '0000-00-00 00:00:00',
	`use_planning` smallint(6) NOT NULL default '1',
	`comments` text,
	`realtime` float DEFAULT '0' NOT NULL,
	`type` int(4) NOT NULL default '0',
	`deleted` smallint(6) NOT NULL default '0',
	`tech_num` int(11) NOT NULL default '0',
PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_dropdown_plugin_activity_type`;
CREATE TABLE `glpi_dropdown_plugin_activity_type` (
	`ID` int(11) NOT NULL auto_increment,
	`parentID` int(11) NOT NULL default '0',
	`name` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
	`completename` text collate utf8_unicode_ci,
	`comments` text collate utf8_unicode_ci,
	`level` int(11) default NULL,
	`FK_profiles` int(4) NOT NULL default '0',
	PRIMARY KEY  (`ID`),
	KEY `name` (`name`),
	KEY `parentID` (`parentID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_activity_profiles`;
CREATE TABLE `glpi_plugin_activity_profiles` (
	`ID` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`interface` varchar(50)  collate utf8_unicode_ci NOT NULL default 'activity',
	`is_default` smallint(6) NOT NULL default '0',
	`activity` char(1) default NULL,
	`statistics` char(1) default NULL,
	`all_users` char(1) default NULL,
	PRIMARY KEY  (`ID`),
	KEY `interface` (`interface`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'1100','2','1','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'1100','3','2','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'1100','4','3','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'1100','6','4','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'1100','7','5','0');