ALTER TABLE `glpi_plugin_manageentities_configs` ADD  `comment` tinyint(1) NOT NULL default '1' COMMENT 'display comments in the CRI';
ALTER TABLE `glpi_plugin_manageentities_companies` ADD  `comment` text collate utf8_unicode_ci;
