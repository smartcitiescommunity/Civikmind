RENAME TABLE glpi_plugin_activity TO glpi_plugin_activity_activities;
ALTER TABLE glpi_plugin_activity_activities change `FK_entities` `entities_id` int(11);
ALTER TABLE glpi_plugin_activity_activities DROP KEY `FK_entities`;
ALTER TABLE glpi_plugin_activity_activities ADD INDEX `entities_id` (`entities_id`);
ALTER TABLE glpi_plugin_activity_activities change `deleted` `is_deleted` tinyint(1);
ALTER TABLE glpi_plugin_activity_activities change `comments` `comment` text;
ALTER TABLE glpi_plugin_activity_activities change `ID` `id` int(11);
ALTER TABLE glpi_plugin_activity_activities change `type` `plugin_activity_activitytypes_id` int(11);
ALTER TABLE glpi_plugin_activity_activities ADD CONSTRAINT plugin_activity_activitytypes_id FOREIGN KEY (plugin_activity_activitytypes_id) references glpi_plugin_activity_activitytypes(id);
ALTER TABLE glpi_plugin_activity_activities ADD `is_usedbycra` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE glpi_plugin_activity_activities ADD `allDay` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE glpi_plugin_activity_activities DROP `is_deleted`;

RENAME TABLE glpi_dropdown_plugin_activity_type TO glpi_plugin_activity_activitytypes;
ALTER TABLE glpi_plugin_activity_activitytypes change `parentID` `plugin_activity_activitytypes_id` int(11);
ALTER TABLE glpi_plugin_activity_activitytypes change `ID` `id` int(11);
ALTER TABLE glpi_plugin_activity_activitytypes DROP KEY `FK_profiles`;
ALTER TABLE glpi_plugin_activity_activitytypes DROP `FK_profiles`;
ALTER TABLE glpi_plugin_activity_activitytypes DROP KEY `parentID`;
ALTER TABLE glpi_plugin_activity_activitytypes ADD `ancestors_cache` text COLLATE utf8_unicode_ci default NULL;
ALTER TABLE glpi_plugin_activity_activitytypes ADD `sons_cache` text COLLATE utf8_unicode_ci default NULL;
ALTER TABLE glpi_plugin_activity_activitytypes ADD CONSTRAINT plugin_activity_activitytypes_id FOREIGN KEY (plugin_activity_activitytypes_id) references glpi_plugin_activity_activitytypes(id);

ALTER TABLE glpi_plugin_activity_profiles change `name` `profiles_id` int(11);
ALTER TABLE glpi_plugin_activity_profiles DROP KEY `name`;
ALTER TABLE glpi_plugin_activity_profiles ADD INDEX `profiles_id` (`profiles_id`);
ALTER TABLE glpi_plugin_activity_profiles change `ID` `id` int(11);

ALTER TABLE glpi_plugin_activity_profiles ADD `can_validate` char(1) NOT NULL DEFAULT '0' AFTER `statistics`;
ALTER TABLE glpi_plugin_activity_profiles ADD `can_requestholiday` char(1) NOT NULL DEFAULT '0' AFTER `statistics`;

DROP TABLE IF EXISTS `glpi_plugin_activity_holidays`;
CREATE TABLE `glpi_plugin_activity_holidays` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
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
   PRIMARY KEY  (`id`),
   KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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

INSERT INTO `glpi_plugin_activity_options` VALUES(NULL, 'Glpi', 'Footer','', 0, 0);

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