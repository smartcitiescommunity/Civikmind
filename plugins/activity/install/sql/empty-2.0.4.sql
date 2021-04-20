DROP TABLE IF EXISTS `glpi_plugin_activity_activities`;
CREATE TABLE `glpi_plugin_activity_activities` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
   `begin_date` DATETIME NULL default NULL,
   `end_date`  DATETIME NULL default NULL,
   `is_planned` tinyint(1) NOT NULL DEFAULT '0',
   `comment` text COLLATE utf8_unicode_ci default NULL,
   `actiontime` int(11) NOT NULL DEFAULT '0',
   `plugin_activity_activitytypes_id` int(4) NOT NULL default '0',
   `is_usedbycra` tinyint(1) NOT NULL DEFAULT '0',
   `users_id` int(11) NOT NULL default '0',
   `allDay` tinyint(1) NOT NULL DEFAULT '0',
   PRIMARY KEY  (`id`),
   KEY `users_id` (`users_id`),
   KEY `plugin_activity_activitytypes_id` (`plugin_activity_activitytypes_id`),
   KEY `entities_id` (`entities_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_activity_activitytypes`;
CREATE TABLE `glpi_plugin_activity_activitytypes` (
   `id` int(11) NOT NULL auto_increment,
   `plugin_activity_activitytypes_id` int(11) NOT NULL default '0',
   `name` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
   `completename` text collate utf8_unicode_ci,
   `comment` text collate utf8_unicode_ci,
   `level` int(11) NOT NULL default '0', -- entites_id
   `ancestors_cache` text COLLATE utf8_unicode_ci default NULL,
   `sons_cache` text COLLATE utf8_unicode_ci default NULL,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`), 
        FOREIGN KEY (`plugin_activity_activitytypes_id`) REFERENCES glpi_plugin_activity_activitytypes(id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `glpi_plugin_activity_holidays`;
CREATE TABLE `glpi_plugin_activity_holidays` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
   `date_mod` DATETIME NULL default NULL,
   `begin_date` DATETIME NULL default NULL,
   `end_date`  DATETIME NULL default NULL,
   `is_planned` tinyint(1) NOT NULL DEFAULT '0',
   `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 -> NONE ; 2 -> Waiting ; 3 -> accepted ; 4 -> rejected',
   `comment` text COLLATE utf8_unicode_ci default NULL,
   `actiontime` int(11) NOT NULL DEFAULT '0',
   `plugin_activity_holidaytypes_id` int(4) NOT NULL default '0',
   `users_id` int(11) NOT NULL default '0',
   `allDay` tinyint(1) NOT NULL DEFAULT '0',
   PRIMARY KEY  (`id`),
   KEY `users_id` (`users_id`),
   KEY `plugin_activity_holidaytypes_id` (`plugin_activity_holidaytypes_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_activity_holidaytypes`;
CREATE TABLE `glpi_plugin_activity_holidaytypes` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
   `comment` text collate utf8_unicode_ci,
   `short_name` varchar(255) collate utf8_unicode_ci default NULL,
   `mandatory_comment` tinyint(1) NOT NULL default 0,
   `auto_validated` tinyint(1) NOT NULL default 0
   PRIMARY KEY  (`id`),
   KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `glpi_plugin_activity_profiles`;
CREATE TABLE `glpi_plugin_activity_profiles` (
   `id` int(11) NOT NULL auto_increment,
   `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)', -- lien avec profiles de glpi
   `activity` char(1) default NULL,
   `statistics` char(1) default NULL,
   `can_requestholiday` char(1) default NULL,
   `can_validate` char(1) default NULL,
   `all_users` char(1) default NULL,
   PRIMARY KEY  (`id`),
   KEY `profiles_id` (`profiles_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
      
-- INSERT INTO `glpi_display` ( `id` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'1100','2','1','0');
-- INSERT INTO `glpi_display` ( `id` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'1100','3','2','0');
-- INSERT INTO `glpi_display` ( `id` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'1100','4','3','0');
-- INSERT INTO `glpi_display` ( `id` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'1100','6','4','0');
-- INSERT INTO `glpi_display` ( `id` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'1100','7','5','0');

DROP TABLE IF EXISTS `glpi_plugin_activity_configs`;
CREATE TABLE `glpi_plugin_activity_configs` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
   `entities_id` int(11) NOT NULL,
   PRIMARY KEY  (`id`),
   FOREIGN KEY (`entities_id`) REFERENCES glpi_entities(id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_activity_options`;
CREATE TABLE `glpi_plugin_activity_options` (
   `id` int(11) NOT NULL auto_increment,
   `principal_client` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
   `cra_footer` text collate utf8_unicode_ci,
   `used_mail_for_holidays` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
   `use_timerepartition` tinyint(1) default 0,
   `use_pairs` tinyint(1) default 0,
   PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_activity_options` VALUES(NULL, 'Glpi', 'Footer', '', 0, 0);

DROP TABLE IF EXISTS `glpi_plugin_activity_tickettasks`;
CREATE TABLE `glpi_plugin_activity_tickettasks` (
   `id` int(11) NOT NULL auto_increment,
   `is_oncra` tinyint(1) default 1,
   `tickettasks_id` int(11) NOT NULL,
   PRIMARY KEY  (`id`),
   FOREIGN KEY (`tickettasks_id`) REFERENCES glpi_tickettasks(id),
   KEY `is_oncra` (`is_oncra`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `glpi_plugin_activity_preferences`;
CREATE TABLE `glpi_plugin_activity_preferences` (
   `id` int(11) NOT NULL auto_increment,
   `users_id` int(11) NOT NULL DEFAULT '0',
   `users_id_validate` int(11) NOT NULL DEFAULT '0',   
   PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_activity_holidayvalidations`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_activity_holidayvalidations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_activity_holidays_id` int(11) NOT NULL DEFAULT '0',
  `users_id_validate` int(11) NOT NULL DEFAULT '0',
  `comment_validation` text COLLATE utf8_unicode_ci,
  `status` tinyint (1) NOT NULL DEFAULT 2 COMMENT '1 -> NONE ; 2 -> Waiting ; 3 -> accepted ; 4 -> rejected',
  `submission_date` datetime DEFAULT NULL,
  `validation_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `users_id_validate` (`users_id_validate`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;


INSERT INTO `glpi_notificationtemplates` VALUES(NULL, 'Holidays validation', 'PluginActivityHoliday', '2014-01-09 08:36:14','',NULL);