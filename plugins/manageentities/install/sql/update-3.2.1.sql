ALTER TABLE `glpi_plugin_manageentities_configs` ADD `non_accomplished_tasks` tinyint(1) NOT NULL default '0';
ALTER TABLE `glpi_plugin_manageentities_configs` ADD `get_pdf_cri` tinyint(1) NOT NULL default '0';
ALTER TABLE `glpi_plugin_manageentities_configs` ADD `ticket_state` int(11) NOT NULL default '3';
ALTER TABLE `glpi_plugin_manageentities_configs` ADD `default_duration` varchar(255) default NULL;
ALTER TABLE `glpi_plugin_manageentities_configs` ADD `default_time_am` varchar(255) default NULL;
ALTER TABLE `glpi_plugin_manageentities_configs` ADD `default_time_pm` varchar(255) default NULL;
