DROP TABLE IF EXISTS `glpi_plugin_activity_activities`;
CREATE TABLE `glpi_plugin_activity_activities` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
   `begin` DATETIME NULL default NULL,
   `end`  DATETIME NULL default NULL,
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
   `begin` DATETIME NULL default NULL,
   `end`  DATETIME NULL default NULL,
   `is_planned` tinyint(1) NOT NULL DEFAULT '0',
   `global_validation` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 -> NONE ; 2 -> Waiting ; 3 -> accepted ; 4 -> rejected',
   `comment` text COLLATE utf8_unicode_ci default NULL,
   `actiontime` int(11) NOT NULL DEFAULT '0',
   `plugin_activity_holidaytypes_id` int(4) NOT NULL default '0',
   `users_id` int(11) NOT NULL default '0',
   `allDay` tinyint(1) NOT NULL DEFAULT '0',
   `validation_percent` int(11) NOT NULL DEFAULT '0',
   `plugin_activity_holidayperiods_id` int(4) default '0',
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
   `mandatory_comment` tinyint(1) NOT NULL default '0',
   `auto_validated` tinyint(1) default '0',
   `is_holiday` tinyint(1) default '1',
   `is_sickness` tinyint(1) default '0',
   `is_part_time` tinyint(1) default '0',
   `is_holiday_counter` tinyint(1) default '0',
   `is_period` tinyint(1) default '0',
   PRIMARY KEY  (`id`),
   KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `glpi_plugin_activity_configs`;
CREATE TABLE `glpi_plugin_activity_configs` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
   `entities_id` int(11) NOT NULL,
   `use_mandaydisplay` tinyint(1) DEFAULT '0',
   `use_integerschedules` tinyint(1) DEFAULT '0',
   PRIMARY KEY  (`id`),
   FOREIGN KEY (`entities_id`) REFERENCES glpi_entities(id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_activity_options`;
CREATE TABLE `glpi_plugin_activity_options` (
   `id` int(11) NOT NULL auto_increment,
   `principal_client` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
   `cra_footer` text collate utf8_unicode_ci,
   `used_mail_for_holidays` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
   `use_type_as_name` tinyint(1) default '0',
   `use_timerepartition` tinyint(1) default '0',
   `use_mandaydisplay` tinyint(1) default '0',
   `use_pairs` tinyint(1) default '0',
   `use_integerschedules` tinyint(1) default '0',
   `use_groupmanager` tinyint(1) default '0',
   `default_validation_percent` int(11) NOT NULL DEFAULT '0',
   `is_cra_default` int(11) NOT NULL DEFAULT '0',
   PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_activity_options` (`principal_client`, `cra_footer`, `used_mail_for_holidays`)
VALUES('Glpi', 'Footer', '');

DROP TABLE IF EXISTS `glpi_plugin_activity_tickettasks`;
CREATE TABLE `glpi_plugin_activity_tickettasks` (
   `id` int(11) NOT NULL auto_increment,
   `is_oncra` tinyint(1) default '1',
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

DROP TABLE IF EXISTS `glpi_plugin_activity_holidaycounts`;
CREATE TABLE `glpi_plugin_activity_holidaycounts` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
   `date_mod` DATETIME NULL default NULL,
   `count` decimal(20,1) NOT NULL DEFAULT '0.00',
   `plugin_activity_holidayperiods_id` int(4) NOT NULL default '0',
   `plugin_activity_holidaytypes_id` int(4) NOT NULL default '0',
   `users_id` int(11) NOT NULL default '0',
   PRIMARY KEY  (`id`),
   KEY `users_id` (`users_id`),
   KEY `plugin_activity_holidayperiods_id` (`plugin_activity_holidayperiods_id`),
   KEY `plugin_activity_holidaytypes_id` (`plugin_activity_holidaytypes_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_activity_holidayperiods`;
CREATE TABLE `glpi_plugin_activity_holidayperiods` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
   `short_name` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   `begin` DATE NULL default NULL,
   `end`  DATE NULL default NULL,
   `archived` tinyint (1) NOT NULL DEFAULT 0,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_activity_snapshots`;
CREATE TABLE `glpi_plugin_activity_snapshots` (
   `id` int(11) NOT NULL auto_increment,
   `documents_id` int(11) NOT NULL,
   `date` DATETIME NULL default NULL,
   `month` int(2) NOT NULL,
   `year` int(5) NOT NULL,
   PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_notificationtemplates` VALUES(NULL, 'Holidays validation', 'PluginActivityHoliday', '2014-01-09 08:36:14','',NULL, '2014-01-09 08:36:14');