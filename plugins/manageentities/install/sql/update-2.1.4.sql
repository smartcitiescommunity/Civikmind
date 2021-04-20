ALTER TABLE `glpi_plugin_manageentities_contracts` ADD `moving_management` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `glpi_plugin_manageentities_contracts` ADD `duration_moving` decimal(20,2) NOT NULL default '0';
ALTER TABLE `glpi_plugin_manageentities_cridetails` ADD  `number_moving` int(11) NOT NULL default '0' COMMENT 'Number of movements';
