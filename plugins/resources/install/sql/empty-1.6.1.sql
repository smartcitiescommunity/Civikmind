DROP TABLE IF EXISTS `glpi_plugin_resources_resources`;
CREATE TABLE `glpi_plugin_resources_resources` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`firstname` varchar(255) collate utf8_unicode_ci default NULL,
	`plugin_resources_contracttypes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_contracttypes (id)',
	`users_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
	`users_id_recipient` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
	`date_declaration` date default NULL,
	`date_begin` date default NULL,
	`date_end` date default NULL,
	`plugin_resources_departments_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_departments (id)',
	`plugin_resources_resourcestates_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_resourcestates (id)',
	`locations_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_locations (id)',
	`is_leaving` int(11) NOT NULL default '0',
	`users_id_recipient_leaving` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
	`picture` varchar(100) collate utf8_unicode_ci default NULL,
	`is_helpdesk_visible` int(11) NOT NULL default '1',
	`date_mod` datetime default NULL,
	`comment` text collate utf8_unicode_ci,
	`notepad` longtext collate utf8_unicode_ci,
	`is_template` tinyint(1) NOT NULL default '0',
	`template_name` varchar(255) collate utf8_unicode_ci default NULL,
	`is_deleted` tinyint(1) NOT NULL default '0',
	PRIMARY KEY  (`id`),
	KEY `name` (`name`),
	KEY `entities_id` (`entities_id`),
	KEY `plugin_resources_contracttypes_id` (`plugin_resources_contracttypes_id`),
	KEY `users_id` (`users_id`),
	KEY `users_id_recipient` (`users_id_recipient`),
	KEY `plugin_resources_departments_id` (`plugin_resources_departments_id`),
	KEY `plugin_resources_resourcestates_id` (`plugin_resources_resourcestates_id`),
	KEY `locations_id` (`locations_id`),
	KEY `is_leaving` (`is_leaving`),
	KEY `users_id_recipient_leaving` (`users_id_recipient_leaving`),
	KEY `date_mod` (`date_mod`),
	KEY `is_helpdesk_visible` (`is_helpdesk_visible`),
	KEY `is_deleted` (`is_deleted`),
	KEY `is_template` (`is_template`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_resourcestates`;
CREATE TABLE `glpi_plugin_resources_resourcestates` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_choices`;
CREATE TABLE `glpi_plugin_resources_choices` (
	`id` int(11) NOT NULL auto_increment,
	`plugin_resources_resources_id` int(11) NOT NULL default '0'COMMENT 'RELATION to glpi_plugin_resources_resources (id)',
	`itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_resources_items`;
CREATE TABLE `glpi_plugin_resources_resources_items` (
	`id` int(11) NOT NULL auto_increment,
	`plugin_resources_resources_id` int(11) NOT NULL default '0'COMMENT 'RELATION to glpi_plugin_resources_resources (id)',
	`items_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various table, according to itemtype (id)',
	`itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	UNIQUE KEY `unicity` (`plugin_resources_resources_id`,`itemtype`,`items_id`),
	KEY `FK_device` (`items_id`,`itemtype`),
	KEY `item` (`itemtype`,`items_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_employees`;
CREATE TABLE `glpi_plugin_resources_employees` (
	`id` int(11) NOT NULL auto_increment,
	`plugin_resources_resources_id` int(11) NOT NULL default '0'COMMENT 'RELATION to glpi_plugin_resources_resources (id)',
	`plugin_resources_employers_id` int(11) NOT NULL default '0'COMMENT 'RELATION to glpi_plugin_resources_employers (id)',
	`plugin_resources_clients_id` int(11) NOT NULL default '0'COMMENT 'RELATION to glpi_plugin_resources_clients (id)',
	`matricule` varchar(255) collate utf8_unicode_ci default NULL,
	PRIMARY KEY  (`id`),
	KEY `plugin_resources_resources_id` (`plugin_resources_resources_id`),
	KEY `plugin_resources_employers_id` (`plugin_resources_employers_id`),
	KEY `plugin_resources_clients_id` (`plugin_resources_clients_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_employers`;
CREATE TABLE `glpi_plugin_resources_employers` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_clients`;
CREATE TABLE `glpi_plugin_resources_clients` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_contracttypes`;
CREATE TABLE `glpi_plugin_resources_contracttypes` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_departments`;
CREATE TABLE `glpi_plugin_resources_departments` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_tasks`;
CREATE TABLE `glpi_plugin_resources_tasks` (
	`id` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`plugin_resources_resources_id` int(11) NOT NULL default '0'COMMENT 'RELATION to glpi_plugin_resources_resources (id)',
	`plugin_resources_tasktypes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_tasktypes (id)',
	`users_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
	`groups_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_groups (id)',
	`date_begin` datetime default NULL,
	`date_end` datetime default NULL,
	`is_planned` tinyint(1) NOT NULL default '0',
	`realtime` float DEFAULT '0' NOT NULL,
	`is_finished` tinyint(1) NOT NULL default '0',
	`comment` text collate utf8_unicode_ci,
	`is_deleted` tinyint(1) NOT NULL default '0',
	PRIMARY KEY  (`id`),
	KEY `plugin_resources_resources_id` (`plugin_resources_resources_id`),
	KEY `plugin_resources_tasktypes_id` (`plugin_resources_tasktypes_id`),
	KEY `users_id` (`users_id`),
	KEY `groups_id` (`groups_id`),
	KEY `is_finished` (`is_finished`),
	KEY `is_deleted` (`is_deleted`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_tasktypes`;
CREATE TABLE `glpi_plugin_resources_tasktypes` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_tasks_items`;
CREATE TABLE `glpi_plugin_resources_tasks_items` (
	`id` int(11) NOT NULL auto_increment,
	`plugin_resources_tasks_id` int(11) NOT NULL default '0',
	`items_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various table, according to itemtype (id)',
	`itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
	PRIMARY KEY  (`id`),
	UNIQUE KEY `unicity` (`plugin_resources_tasks_id`,`itemtype`,`items_id`),
	KEY `FK_device` (`items_id`,`itemtype`),
	KEY `item` (`itemtype`,`items_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_checklists`;
CREATE TABLE `glpi_plugin_resources_checklists` (
	`id` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`entities_id` int(11) NOT NULL default '0',
	`plugin_resources_resources_id` int(11) NOT NULL default '0'COMMENT 'RELATION to glpi_plugin_resources_resources (id)',
	`plugin_resources_tasks_id` int(11) NOT NULL default '0'COMMENT 'RELATION to glpi_plugin_resources_tasks (id)',
	`plugin_resources_contracttypes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_contracttypes (id)',
	`checklist_type` int(11) NOT NULL default '0',
	`tag` tinyint(1) NOT NULL default '0',
	`is_checked` tinyint(1) NOT NULL default '0',
	`address` varchar(255) collate utf8_unicode_ci default NULL,
	`rank` smallint(6) NOT NULL default '0',
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`),
	KEY `plugin_resources_resources_id` (`plugin_resources_resources_id`),
	KEY `plugin_resources_tasks_id` (`plugin_resources_tasks_id`),
	KEY `plugin_resources_contracttypes_id` (`plugin_resources_contracttypes_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_ticketcategories`;
CREATE TABLE `glpi_plugin_resources_ticketcategories` (
	`id` int(11) NOT NULL auto_increment,
	`ticketcategories_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_ticketcategories (id)',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_profiles`;
CREATE TABLE `glpi_plugin_resources_profiles` (
	`id` int(11) NOT NULL auto_increment,
	`profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
	`resources` char(1) collate utf8_unicode_ci default NULL,
	`task` char(1) collate utf8_unicode_ci default NULL,
	`checklist` char(1) collate utf8_unicode_ci default NULL,
	`all` char(1) collate utf8_unicode_ci default NULL,
	`employee` char(1) collate utf8_unicode_ci default NULL,
	`open_ticket` char(1) collate utf8_unicode_ci default NULL,
	PRIMARY KEY  (`id`),
	KEY `profiles_id` (`profiles_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResource','2','1','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResource','3','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResource','4','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResource','5','4','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResource','6','5','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResource','8','6','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesTask','2','1','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesTask','3','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesTask','4','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesTask','6','4','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesTask','7','5','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesHelpdesk','2','1','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesHelpdesk','3','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesHelpdesk','4','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesHelpdesk','6','5','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesHelpdesk','7','6','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesHelpdesk','8','7','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesHelpdesk','9','8','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','3','1','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','4','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','5','5','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','6','4','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','7','7','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','8','6','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','9','8','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','10','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','11','9','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','12','10','0');

INSERT INTO `glpi_notificationtemplates` VALUES(NULL, 'Resources', 'PluginResourcesResource', '2010-05-17 22:36:46','');
INSERT INTO `glpi_notificationtemplates` VALUES(NULL, 'Alert Resources Tasks', 'PluginResourcesResource', '2010-05-17 22:36:46','');
INSERT INTO `glpi_notificationtemplates` VALUES(NULL, 'Alert Leaving Resources', 'PluginResourcesResource', '2010-05-17 22:36:46','');
INSERT INTO `glpi_notificationtemplates` VALUES(NULL, 'Alert Resources Checklists', 'PluginResourcesResource', '2010-05-17 22:36:46','');
INSERT INTO `glpi_notificationtemplates` VALUES(NULL, 'Leaving Resource', 'PluginResourcesResource', '2010-05-17 22:36:46','');