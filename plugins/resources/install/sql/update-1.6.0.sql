ALTER TABLE `glpi_plugin_resources` RENAME `glpi_plugin_resources_resources`;
ALTER TABLE `glpi_plugin_resources_needs` RENAME `glpi_plugin_resources_choices`;
ALTER TABLE `glpi_plugin_resources_device` RENAME `glpi_plugin_resources_resources_items`;
ALTER TABLE `glpi_plugin_resources_employee` RENAME `glpi_plugin_resources_employees`;
ALTER TABLE `glpi_dropdown_plugin_resources_employer` RENAME `glpi_plugin_resources_employers`;
ALTER TABLE `glpi_dropdown_plugin_resources_client` RENAME `glpi_plugin_resources_clients`;
ALTER TABLE `glpi_dropdown_plugin_resources_type` RENAME `glpi_plugin_resources_contracttypes`;
ALTER TABLE `glpi_dropdown_plugin_resources_department` RENAME `glpi_plugin_resources_departments`;
ALTER TABLE `glpi_dropdown_plugin_resources_tasks_type` RENAME `glpi_plugin_resources_tasktypes`;
ALTER TABLE `glpi_plugin_resources_tasks_items` RENAME `glpi_plugin_resources_tasks_items`;
DROP TABLE IF EXISTS `glpi_plugin_resources_mailing`;

UPDATE `glpi_plugin_resources_resources` SET `FK_users` = '0' WHERE `FK_users` IS NULL;

ALTER TABLE `glpi_plugin_resources_resources` 
   DROP INDEX `deleted`,
   DROP INDEX `end_date`,
   DROP INDEX `leaving`,
   DROP INDEX `FK_users`,
   DROP INDEX `recipient`,
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `firstname` `firstname` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `type` `plugin_resources_contracttypes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_contracttypes (id)',
   CHANGE `FK_users` `users_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
   CHANGE `recipient` `users_id_recipient` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
   CHANGE `date` `date_declaration` date default NULL,
   CHANGE `begin_date` `date_begin` date default NULL,
   CHANGE `end_date` `date_end` date default NULL,
   CHANGE `department` `plugin_resources_departments_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_departments (id)',
   CHANGE `location` `locations_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_locations (id)',
   CHANGE `leaving` `is_leaving` int(11) NOT NULL default '0',
   CHANGE `recipient_leaving` `users_id_recipient_leaving` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
   CHANGE `helpdesk_visible` `is_helpdesk_visible` int(11) NOT NULL default '1',
   CHANGE `comments` `comment` text collate utf8_unicode_ci,
   CHANGE `notes` `notepad` longtext collate utf8_unicode_ci,
   CHANGE `is_template` `is_template` tinyint(1) NOT NULL default '0',
   CHANGE `tplname` `template_name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL default '0',
   ADD INDEX (`entities_id`),
   ADD INDEX (`plugin_resources_contracttypes_id`),
   ADD INDEX (`users_id`),
   ADD INDEX (`users_id_recipient`),
   ADD INDEX (`plugin_resources_departments_id`),
   ADD INDEX (`locations_id`),
   ADD INDEX (`is_leaving`),
   ADD INDEX (`users_id_recipient_leaving`),
   ADD INDEX (`date_mod`),
   ADD INDEX (`is_helpdesk_visible`),
   ADD INDEX (`is_deleted`),
   ADD INDEX (`is_template`);

ALTER TABLE `glpi_plugin_resources_choices` 
   DROP INDEX `FK_resources`,
   DROP INDEX `FK_resources_2`,
   DROP INDEX `FK_device`,
   DROP INDEX `is_template`,
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_resources` `plugin_resources_resources_id` int(11) NOT NULL default '0'COMMENT 'RELATION to glpi_plugin_resources_resources (id)',
   CHANGE `device_type` `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
   CHANGE `comments` `comment` text collate utf8_unicode_ci,
   DROP `is_template`;

ALTER TABLE `glpi_plugin_resources_resources_items` 
   DROP INDEX `FK_resources`,
   DROP INDEX `FK_resources_2`,
   DROP INDEX `FK_device`,
   DROP INDEX `is_template`,
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_resources` `plugin_resources_resources_id` int(11) NOT NULL default '0'COMMENT 'RELATION to glpi_plugin_resources_resources (id)',
   CHANGE `FK_device` `items_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various table, according to itemtype (id)',
   CHANGE `device_type` `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
   CHANGE `comments` `comment` text collate utf8_unicode_ci,
   DROP `is_template`,
   ADD UNIQUE `unicity` (`plugin_resources_resources_id`,`itemtype`,`items_id`),
   ADD INDEX `FK_device` (`items_id`,`itemtype`),
   ADD INDEX `item` (`itemtype`,`items_id`);

ALTER TABLE `glpi_plugin_resources_employees` DROP INDEX `FK_resources`;

ALTER TABLE `glpi_plugin_resources_employees` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_resources` `plugin_resources_resources_id` int(11) NOT NULL default '0'COMMENT 'RELATION to glpi_plugin_resources_resources (id)',
   CHANGE `FK_employer` `plugin_resources_employers_id` int(11) NOT NULL default '0'COMMENT 'RELATION to glpi_plugin_resources_employers (id)',
   CHANGE `FK_client` `plugin_resources_clients_id` int(11) NOT NULL default '0'COMMENT 'RELATION to glpi_plugin_resources_clients (id)',
   CHANGE `matricule` `matricule` varchar(255) collate utf8_unicode_ci default NULL,
   DROP `is_template`,
   ADD INDEX (`plugin_resources_resources_id`),
   ADD INDEX (`plugin_resources_employers_id`),
   ADD INDEX (`plugin_resources_clients_id`);

ALTER TABLE `glpi_plugin_resources_employers` 
   DROP INDEX `FK_entities`,
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `comments` `comment` text collate utf8_unicode_ci;

ALTER TABLE `glpi_plugin_resources_clients` 
   DROP INDEX `FK_entities`,
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `comments` `comment` text collate utf8_unicode_ci;

ALTER TABLE `glpi_plugin_resources_contracttypes` 
   DROP INDEX `FK_entities`,
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `comments` `comment` text collate utf8_unicode_ci;

ALTER TABLE `glpi_plugin_resources_departments` 
   DROP INDEX `FK_entities`,
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `comments` `comment` text collate utf8_unicode_ci;

ALTER TABLE `glpi_plugin_resources_tasks` 
   DROP INDEX `FK_resources`,
   DROP INDEX `end_date`,
   DROP INDEX `deleted`,
   DROP INDEX `installed`,
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `FK_resources` `plugin_resources_resources_id` int(11) NOT NULL default '0'COMMENT 'RELATION to glpi_plugin_resources_resources (id)',
   CHANGE `type_task` `plugin_resources_tasktypes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_taskstypes (id)',
   CHANGE `assign` `users_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
   CHANGE `assign_group` `groups_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_groups (id)',
   CHANGE `begin_date` `date_begin` datetime default NULL,
   CHANGE `end_date` `date_end` datetime default NULL,
   CHANGE `use_planning` `is_planned` tinyint(1) NOT NULL default '0',
   CHANGE `installed` `is_finished` tinyint(1) NOT NULL default '0',
   CHANGE `comments` `comment` text collate utf8_unicode_ci,
   CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL default '0',
   ADD INDEX (`plugin_resources_resources_id`),
   ADD INDEX (`plugin_resources_tasktypes_id`),
   ADD INDEX (`users_id`),
   ADD INDEX (`groups_id`),
   ADD INDEX (`is_finished`),
   ADD INDEX (`is_deleted`);

ALTER TABLE `glpi_plugin_resources_tasktypes` 
   DROP INDEX `FK_entities`,
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `comments` `comment` text collate utf8_unicode_ci;

ALTER TABLE `glpi_plugin_resources_tasks_items` 
   DROP INDEX `FK_device`,
   DROP INDEX `FK_device_2`,
   DROP INDEX `FK_task`,
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_task` `plugin_resources_tasks_id` int(11) NOT NULL default '0',
   CHANGE `FK_device` `items_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various table, according to itemtype (id)',
   CHANGE `device_type` `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
   DROP `is_template`,
   ADD UNIQUE `unicity` (`plugin_resources_tasks_id`,`itemtype`,`items_id`),
   ADD INDEX `FK_device` (`items_id`,`itemtype`),
   ADD INDEX `item` (`itemtype`,`items_id`);

ALTER TABLE `glpi_plugin_resources_checklists` 
   DROP INDEX `FK_resources`,
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `FK_resources` `plugin_resources_resources_id` int(11) NOT NULL default '0'COMMENT 'RELATION to glpi_plugin_resources_resources (id)',
   CHANGE `FK_task` `plugin_resources_tasks_id` int(11) NOT NULL default '0'COMMENT 'RELATION to glpi_plugin_resources_tasks (id)',
   CHANGE `type` `plugin_resources_contracttypes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_contracttypes (id)',
   CHANGE `checked` `is_checked` tinyint(1) NOT NULL default '0',
   ADD `tag` tinyint(1) NOT NULL default '0',
   CHANGE `address` `address` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `comments` `comment` text collate utf8_unicode_ci,
   DROP `is_default`,
   ADD INDEX (`plugin_resources_tasks_id`),
   ADD INDEX (`plugin_resources_contracttypes_id`);

DROP TABLE IF EXISTS `glpi_plugin_resources_ticketcategories`;
CREATE TABLE `glpi_plugin_resources_ticketcategories` (
	`id` int(11) NOT NULL auto_increment,
	`ticketcategories_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_ticketcategories (id)',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `glpi_plugin_resources_profiles` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   ADD `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
   CHANGE `resources` `resources` char(1) collate utf8_unicode_ci default NULL,
   CHANGE `task` `task` char(1) collate utf8_unicode_ci default NULL,
   ADD `checklist` char(1) collate utf8_unicode_ci default NULL,
   CHANGE `all` `all` char(1) collate utf8_unicode_ci default NULL,
   CHANGE `employer` `employee` char(1) collate utf8_unicode_ci default NULL,
   CHANGE `open_ticket` `open_ticket` char(1) collate utf8_unicode_ci default NULL,
   ADD INDEX (`profiles_id`);

UPDATE `glpi_plugin_resources_profiles` SET `checklist`='w' WHERE `resources` ='w';
   
INSERT INTO `glpi_notificationtemplates` VALUES(NULL, 'Resources', 'PluginResourcesResource', '2010-05-17 22:36:46','');
INSERT INTO `glpi_notificationtemplates` VALUES(NULL, 'Alert Resources Tasks', 'PluginResourcesResource', '2010-05-17 22:36:46','');
INSERT INTO `glpi_notificationtemplates` VALUES(NULL, 'Alert Leaving Resources', 'PluginResourcesResource', '2010-05-17 22:36:46','');
INSERT INTO `glpi_notificationtemplates` VALUES(NULL, 'Alert Resources Checklists', 'PluginResourcesResource', '2010-05-17 22:36:46','');
INSERT INTO `glpi_notificationtemplates` VALUES(NULL, 'Leaving Resource', 'PluginResourcesResource', '2010-05-17 22:36:46','');