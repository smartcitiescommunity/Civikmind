DROP TABLE IF EXISTS `glpi_plugin_resources_transferentities`;
CREATE TABLE `glpi_plugin_resources_transferentities` (
  `id` int(11) NOT NULL auto_increment,
  `entities_id` int(11) NOT NULL default '0',
  `groups_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `entities_id` (`entities_id`),
  KEY `groups_id` (`groups_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `glpi_plugin_resources_reportconfigs` ADD `send_transfer_notif` tinyint(1) NOT NULL default '0';