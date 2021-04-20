DROP TABLE IF EXISTS `glpi_plugin_resources_choiceitems`;
CREATE TABLE `glpi_plugin_resources_choiceitems` (
	`id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
	`is_recursive` tinyint(1) NOT NULL DEFAULT '0',
	`plugin_resources_choiceitems_id` int(11) NOT NULL DEFAULT '0',
   `completename` text COLLATE utf8_unicode_ci,
   `level` int(11) NOT NULL DEFAULT '0',
   `ancestors_cache` longtext COLLATE utf8_unicode_ci,
   `sons_cache` longtext COLLATE utf8_unicode_ci,
	`is_helpdesk_visible` int(11) NOT NULL default '1',
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   UNIQUE KEY `unicity` (`entities_id`,`plugin_resources_choiceitems_id`,`name`),
	KEY `name` (`name`),
	KEY `plugin_resources_choiceitems_id` (`plugin_resources_choiceitems_id`),
	KEY `is_recursive` (`is_recursive`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `glpi_plugin_resources_choices`
   ADD `plugin_resources_choiceitems_id` int(11) NOT NULL default '0'COMMENT 'RELATION to glpi_plugin_resources_choiceitems (id)',
   ADD INDEX (`plugin_resources_choiceitems_id`);
   
ALTER TABLE `glpi_plugin_resources_contracttypes`
   ADD `use_employee_wizard` tinyint(1) NOT NULL DEFAULT '1',
	ADD `use_need_wizard` tinyint(1) NOT NULL DEFAULT '1',
	ADD `use_picture_wizard` tinyint(1) NOT NULL DEFAULT '1';