ALTER TABLE `glpi_plugin_resources_reportconfigs`
   ADD `send_report_notif` tinyint(1) NOT NULL default '1',
   ADD `send_other_notif` tinyint(1) NOT NULL default '0';

DROP TABLE IF EXISTS `glpi_plugin_resources_notifications`;
CREATE TABLE `glpi_plugin_resources_notifications` (
  `id` int(11) NOT NULL auto_increment,
  `plugin_resources_resources_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_resources (id)',
  `date_mod` datetime default NULL,
  `users_id` int(11) NOT NULL default '0',
  `type` varchar(50) default NULL,
  PRIMARY KEY  (`id`),
  KEY `users_id` (`users_id`),
  KEY `date_mod` (`date_mod`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;