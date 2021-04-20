CREATE TABLE `glpi_plugin_resources_checklistconfigs` (
	`id` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`entities_id` int(11) NOT NULL default '0',
	`tag` tinyint(1) NOT NULL default '0',
	`address` varchar(255) collate utf8_unicode_ci default NULL,
	`comment` text collate utf8_unicode_ci,
	`is_deleted` tinyint(1) NOT NULL default '0',
	PRIMARY KEY  (`id`),
	KEY `name` (`name`),
	KEY `is_deleted` (`is_deleted`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `glpi_plugin_resources_contracttypes` 
   ADD `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   ADD INDEX (`entities_id`),
   ADD INDEX (`is_recursive`);

INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesChecklistconfig','2','1','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesChecklistconfig','3','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesChecklistconfig','4','3','0');

ALTER TABLE `glpi_plugin_resources_resources`
   ADD `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   ADD INDEX (`is_recursive`);
   
ALTER TABLE `glpi_plugin_resources_departments` 
   ADD `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   ADD INDEX (`entities_id`),
   ADD INDEX (`is_recursive`);

ALTER TABLE `glpi_plugin_resources_resourcestates` 
   ADD `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   ADD INDEX (`entities_id`),
   ADD INDEX (`is_recursive`);

ALTER TABLE `glpi_plugin_resources_employers` 
   ADD `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   ADD INDEX (`entities_id`),
   ADD INDEX (`is_recursive`);

ALTER TABLE `glpi_plugin_resources_clients` 
   ADD `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   ADD INDEX (`entities_id`),
   ADD INDEX (`is_recursive`);
   
ALTER TABLE `glpi_plugin_resources_tasktypes` 
   ADD `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   ADD INDEX (`entities_id`),
   ADD INDEX (`is_recursive`);
  
ALTER TABLE `glpi_plugin_resources_tasks` 
   ADD `entities_id` int(11) NOT NULL default '0',
   ADD `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   ADD INDEX (`entities_id`),
   ADD INDEX (`is_recursive`);

CREATE TABLE `glpi_plugin_resources_taskplannings` (
  `id` int(11) NOT NULL auto_increment,
  `plugin_resources_tasks_id` int(11) NOT NULL default '0'COMMENT 'RELATION to glpi_plugin_resources_tasks (id)',
  `begin` datetime default NULL,
  `end` datetime default NULL,
  PRIMARY KEY  (`id`),
  KEY `begin` (`begin`),
  KEY `end` (`end`),
  KEY `plugin_resources_tasks_id` (`plugin_resources_tasks_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `glpi_plugin_resources_tasks` DROP `is_planned`;

DROP TABLE IF EXISTS `glpi_plugin_resources_resourcerestings`;
CREATE TABLE `glpi_plugin_resources_resourcerestings` (
	`id` int(11) NOT NULL auto_increment,
	`plugin_resources_resources_id` int(11) NOT NULL default '0'COMMENT 'RELATION to glpi_plugin_resources_resources (id)',
	`date_begin` date default NULL,
	`date_end` date default NULL,
	`locations_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_locations (id)',
	`at_home` tinyint(1) NOT NULL default '0',
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_resourceholidays`;
CREATE TABLE `glpi_plugin_resources_resourceholidays` (
	`id` int(11) NOT NULL auto_increment,
	`plugin_resources_resources_id` int(11) NOT NULL default '0'COMMENT 'RELATION to glpi_plugin_resources_resources (id)',
	`date_begin` date default NULL,
	`date_end` date default NULL,
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `glpi_plugin_resources_profiles` 
   ADD `resting` char(1) collate utf8_unicode_ci default NULL,
   ADD `holiday` char(1) collate utf8_unicode_ci default NULL;

UPDATE `glpi_plugin_resources_profiles` SET `resting`='w' WHERE `resources` ='w';
UPDATE `glpi_plugin_resources_profiles` SET `holiday`='w' WHERE `resources` ='w';

DELETE FROM `glpi_displaypreferences` WHERE `itemtype` = 'PluginResourcesHelpdesk';
DELETE FROM `glpi_displaypreferences` WHERE `itemtype` = 'PluginResourcesDirectory';

INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','34','1','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','9','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','4320','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','3','4','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','5','5','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','10','6','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','6','7','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','11','8','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','4313','9','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','4314','10','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','4316','11','0');

INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResourceResting','2','1','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResourceResting','3','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResourceResting','4','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResourceResting','5','4','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResourceResting','6','5','0');

INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResourceHoliday','2','1','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResourceHoliday','3','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResourceHoliday','4','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResourceHoliday','5','4','0');

INSERT INTO `glpi_notificationtemplates` VALUES(NULL, 'Resource Resting', 'PluginResourcesResource', '2010-05-17 22:36:46','',NULL);
INSERT INTO `glpi_notificationtemplates` VALUES(NULL, 'Resource Holiday', 'PluginResourcesResource', '2010-05-17 22:36:46','',NULL);
