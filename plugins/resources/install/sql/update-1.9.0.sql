ALTER TABLE `glpi_plugin_resources_resources`
   ADD `quota` decimal(10,4) NOT NULL default '1.00',
   ADD `plugin_resources_resourcesituations_id` int(11) NOT NULL default '0',
   ADD `plugin_resources_contractnatures_id` int(11) NOT NULL default '0',
   ADD `plugin_resources_ranks_id` int(11) NOT NULL default '0',
   ADD `plugin_resources_resourcespecialities_id` int(11) NOT NULL default '0',
   ADD `plugin_resources_leavingreasons_id` int(11) NOT NULL default '0',
   ADD INDEX (`plugin_resources_resourcesituations_id`),
   ADD INDEX (`plugin_resources_contractnatures_id`),
   ADD INDEX (`plugin_resources_ranks_id`),
   ADD INDEX (`plugin_resources_resourcespecialities_id`),
   ADD INDEX (`plugin_resources_leavingreasons_id`);

ALTER TABLE `glpi_plugin_resources_choices`
   ADD INDEX (`plugin_resources_resources_id`),
   ADD INDEX (`plugin_resources_choiceitems_id`);

ALTER TABLE `glpi_plugin_resources_choiceitems`
   ADD INDEX (`entities_id`);

ALTER TABLE `glpi_plugin_resources_employers`
   ADD `plugin_resources_employers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_employers (id)',
   ADD `short_name` varchar(255) collate utf8_unicode_ci default NULL,
   ADD `locations_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_locations (id)',
   ADD `code` varchar(255) collate utf8_unicode_ci default NULL,
   ADD `level` int(11) NOT NULL DEFAULT '0',
   ADD `completename` text collate utf8_unicode_ci default NULL,
   ADD `ancestors_cache` longtext collate utf8_unicode_ci default NULL,
   ADD `sons_cache` longtext collate utf8_unicode_ci default NULL,
   ADD INDEX (`locations_id`),
   ADD INDEX (`plugin_resources_employers_id`);

ALTER TABLE `glpi_plugin_resources_contracttypes`
   ADD `code` varchar(255) collate utf8_unicode_ci default NULL;

ALTER TABLE `glpi_plugin_resources_tasks`
   ADD INDEX (`name`);

ALTER TABLE `glpi_plugin_resources_checklists`
   ADD INDEX (`entities_id`);

ALTER TABLE `glpi_plugin_resources_checklistconfigs`
   ADD INDEX (`entities_id`);

ALTER TABLE `glpi_plugin_resources_profiles`
   ADD `employment` char(1) collate utf8_unicode_ci default NULL,
   ADD `budget` char(1) collate utf8_unicode_ci default NULL,
   ADD `dropdown_public` char(1) collate utf8_unicode_ci default NULL;

ALTER TABLE `glpi_plugin_resources_reportconfigs`
   ADD INDEX (`plugin_resources_resources_id`);

ALTER TABLE `glpi_plugin_resources_resourcerestings`
   ADD INDEX (`plugin_resources_resources_id`),
   ADD INDEX (`locations_id`);

ALTER TABLE `glpi_plugin_resources_resourceholidays`
   ADD INDEX (`plugin_resources_resources_id`);

DROP TABLE IF EXISTS `glpi_plugin_resources_resourcesituations`;
CREATE TABLE `glpi_plugin_resources_resourcesituations` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`is_recursive` tinyint(1) NOT NULL DEFAULT '0',
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`code` varchar(255) collate utf8_unicode_ci default NULL,
	`short_name` varchar(255) collate utf8_unicode_ci default NULL,
	`is_contract_linked` tinyint(1) NOT NULL DEFAULT '0',
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`),
	KEY `entities_id` (`entities_id`),
	KEY `is_recursive` (`is_recursive`),
	KEY `is_contract_linked` (`is_contract_linked`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_contractnatures`;
CREATE TABLE `glpi_plugin_resources_contractnatures` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`is_recursive` tinyint(1) NOT NULL DEFAULT '0',
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`code` varchar(255) collate utf8_unicode_ci default NULL,
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`),
	KEY `entities_id` (`entities_id`),
	KEY `is_recursive` (`is_recursive`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_ranks`;
CREATE TABLE `glpi_plugin_resources_ranks` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`is_recursive` tinyint(1) NOT NULL DEFAULT '0',
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`code` varchar(255) collate utf8_unicode_ci default NULL,
	`short_name` varchar(255) collate utf8_unicode_ci default NULL,
	`plugin_resources_professions_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_professions (id)',
	`is_active` tinyint(1) NOT NULL default '0',
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`),
	KEY `entities_id` (`entities_id`),
	KEY `is_recursive` (`is_recursive`),
	KEY `plugin_resources_professions_id` (`plugin_resources_professions_id`),
	KEY `is_active` (`is_active`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `glpi_plugin_resources_resourcespecialities`;
CREATE TABLE `glpi_plugin_resources_resourcespecialities` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`is_recursive` tinyint(1) NOT NULL DEFAULT '0',
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`plugin_resources_ranks_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_ranks (id)',
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`),
	KEY `entities_id` (`entities_id`),
	KEY `is_recursive` (`is_recursive`),
	KEY `plugin_resources_ranks_id` (`plugin_resources_ranks_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_leavingreasons`;
CREATE TABLE `glpi_plugin_resources_leavingreasons` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`is_recursive` tinyint(1) NOT NULL DEFAULT '0',
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`),
	KEY `entities_id` (`entities_id`),
	KEY `is_recursive` (`is_recursive`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_professions`;
CREATE TABLE `glpi_plugin_resources_professions` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`is_recursive` tinyint(1) NOT NULL DEFAULT '0',
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`code` varchar(255) collate utf8_unicode_ci default NULL,
	`short_name` varchar(255) collate utf8_unicode_ci default NULL,
	`plugin_resources_professionlines_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_professionlines (id)',
	`plugin_resources_professioncategories_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_professioncategories (id)',
	`is_active` tinyint(1) NOT NULL default '0',
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`),
	KEY `entities_id` (`entities_id`),
	KEY `is_recursive` (`is_recursive`),
	KEY `plugin_resources_professionlines_id` (`plugin_resources_professionlines_id`),
	KEY `plugin_resources_professioncategories_id` (`plugin_resources_professioncategories_id`),
	KEY `is_active` (`is_active`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_professionlines`;
CREATE TABLE `glpi_plugin_resources_professionlines` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`is_recursive` tinyint(1) NOT NULL DEFAULT '0',
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`code` varchar(255) collate utf8_unicode_ci default NULL,
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`),
	KEY `entities_id` (`entities_id`),
	KEY `is_recursive` (`is_recursive`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_professioncategories`;
CREATE TABLE `glpi_plugin_resources_professioncategories` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`is_recursive` tinyint(1) NOT NULL DEFAULT '0',
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`code` varchar(255) collate utf8_unicode_ci default NULL,
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`),
	KEY `entities_id` (`entities_id`),
	KEY `is_recursive` (`is_recursive`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_employments`;
CREATE TABLE `glpi_plugin_resources_employments` (
	`id` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`entities_id` int(11) NOT NULL default '0',
	`is_recursive` tinyint(1) NOT NULL DEFAULT '0',
	`plugin_resources_resources_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_resources (id)',
	`plugin_resources_professions_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_professions (id)',
	`plugin_resources_ranks_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_ranks (id)',
	`plugin_resources_employmentstates_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_employmentstates (id)',
	`plugin_resources_employers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_employers (id)',
	`ratio_employment_budget` decimal(10,2) NOT NULL default '0',
	`begin_date` date default NULL,
	`end_date` date default NULL,
	`date_mod` datetime default NULL,
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`),
	KEY `plugin_resources_resources_id` (`plugin_resources_resources_id`),
	KEY `plugin_resources_professions_id` (`plugin_resources_professions_id`),
	KEY `plugin_resources_ranks_id` (`plugin_resources_ranks_id`),
	KEY `plugin_resources_employmentstates_id` (`plugin_resources_employmentstates_id`),
	KEY `plugin_resources_employers_id` (`plugin_resources_employers_id`),
	KEY `entities_id` (`entities_id`),
	KEY `date_mod` (`date_mod`),
	KEY `is_recursive` (`is_recursive`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_employmentstates`;
CREATE TABLE `glpi_plugin_resources_employmentstates` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`is_recursive` tinyint(1) NOT NULL DEFAULT '0',
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`short_name` varchar(255) collate utf8_unicode_ci default NULL,
	`is_active` tinyint(1) NOT NULL default '0',
	`is_leaving_state` tinyint(1) NOT NULL default '0',
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`),
	KEY `entities_id` (`entities_id`),
	KEY `is_recursive` (`is_recursive`),
	KEY `is_active` (`is_active`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_budgets`;
CREATE TABLE `glpi_plugin_resources_budgets` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`is_recursive` tinyint(1) NOT NULL DEFAULT '0',
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`plugin_resources_professions_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_professions (id)',
	`plugin_resources_ranks_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_resources_ranks (id)',
	`plugin_resources_budgettypes_id` int(11) NOT NULL default '0'COMMENT 'RELATION to glpi_plugin_resources_budgettypes (id)',
	`plugin_resources_budgetvolumes_id` int(11) NOT NULL default '0'COMMENT 'RELATION to glpi_plugin_resources_budgetvolumes (id)',
	`begin_date` date default NULL,
	`end_date` date default NULL,
	`volume` int(11) NOT NULL default '0',
	`date_mod` datetime default NULL,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`),
	KEY `plugin_resources_professions_id` (`plugin_resources_professions_id`),
	KEY `plugin_resources_ranks_id` (`plugin_resources_ranks_id`),
	KEY `plugin_resources_budgettypes_id` (`plugin_resources_budgettypes_id`),
	KEY `plugin_resources_budgetvolumes_id` (`plugin_resources_budgetvolumes_id`),
	KEY `date_mod` (`date_mod`),
	KEY `entities_id` (`entities_id`),
	KEY `is_recursive` (`is_recursive`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_costs`;
CREATE TABLE `glpi_plugin_resources_costs` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`is_recursive` tinyint(1) NOT NULL DEFAULT '0',
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`plugin_resources_professions_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_professions (id)',
	`plugin_resources_ranks_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_resources_ranks (id)',
	`begin_date` date default NULL,
	`end_date` date default NULL,
	`cost` decimal(10,2) NOT NULL default '0',
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`),
	KEY `plugin_resources_professions_id` (`plugin_resources_professions_id`),
	KEY `plugin_resources_ranks_id` (`plugin_resources_ranks_id`),
	KEY `entities_id` (`entities_id`),
	KEY `is_recursive` (`is_recursive`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_budgettypes`;
CREATE TABLE `glpi_plugin_resources_budgettypes` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`is_recursive` tinyint(1) NOT NULL DEFAULT '0',
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`code` varchar(255) collate utf8_unicode_ci default NULL,
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`),
	KEY `entities_id` (`entities_id`),
	KEY `is_recursive` (`is_recursive`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
--
DROP TABLE IF EXISTS `glpi_plugin_resources_budgetvolumes`;
CREATE TABLE `glpi_plugin_resources_budgetvolumes` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`is_recursive` tinyint(1) NOT NULL DEFAULT '0',
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`code` varchar(255) collate utf8_unicode_ci default NULL,
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`),
	KEY `entities_id` (`entities_id`),
	KEY `is_recursive` (`is_recursive`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesEmployment','9','1','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesEmployment','5','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesEmployment','4','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesEmployment','6','4','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesEmployment','7','5','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesEmployment','8','6','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesEmployment','10','7','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesBudget','6','1','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesBudget','7','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesBudget','4','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesBudget','3','4','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesBudget','5','5','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesBudget','8','6','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesBudget','9','7','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4350','1','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4351','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4352','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4353','4','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4354','5','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4355','6','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4356','7','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4357','8','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4358','9','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4359','10','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4360','11','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4361','12','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4362','13','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4363','14','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4364','15','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4365','16','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4366','17','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4367','18','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4368','19','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4369','20','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4370','21','0');
