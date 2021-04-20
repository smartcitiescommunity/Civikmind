INSERT INTO `glpi_notificationtemplates` VALUES(NULL, 'Resource Report Creation', 'PluginResourcesResource', '2010-11-16 11:36:46','');

CREATE TABLE `glpi_plugin_resources_reportconfigs` (
	`id` int(11) NOT NULL auto_increment,
	`plugin_resources_resources_id` int(11) NOT NULL default '0'COMMENT 'RELATION to glpi_plugin_resources_resources (id)',
	`comment` text collate utf8_unicode_ci,
	`information` text collate utf8_unicode_ci,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

UPDATE `glpi_plugin_resources_choices` SET `itemtype` = '4303' WHERE `itemtype` = 'PluginResourcesDirectory';