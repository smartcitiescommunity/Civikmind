DROP TABLE IF EXISTS `glpi_plugin_manageentity_preference`;
CREATE TABLE `glpi_plugin_manageentity_preference` (
	`ID` int(11) NOT NULL auto_increment,
	`user_id` int(11) NOT NULL,
	`show` varchar(255) NOT NULL,
	PRIMARY KEY  (`ID`)
) ENGINE=MyISAM;