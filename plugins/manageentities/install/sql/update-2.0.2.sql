
DROP TABLE IF EXISTS `glpi_plugin_manageentities_interventionskateholders`;
CREATE TABLE `glpi_plugin_manageentities_interventionskateholders` (
   `id` int(11) NOT NULL auto_increment,
   `users_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
   `number_affected_days` double NOT NULL default '0' COMMENT 'Number of days affected to the user to an intervention',
   `plugin_manageentities_contractdays_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_manageentities_contractdays (id)',
   PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `glpi_plugin_manageentities_contracts` ADD  `show_on_global_gantt` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `glpi_plugin_manageentities_contractdays` ADD  `charged` tinyint(1) NOT NULL DEFAULT '0';
