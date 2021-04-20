DROP TABLE IF EXISTS `glpi_plugin_activity_snapshots`;
CREATE TABLE `glpi_plugin_activity_snapshots` (
   `id` int(11) NOT NULL auto_increment,
   `documents_id` int(11) NOT NULL,
   `date` DATETIME NULL default NULL,
   `month` int(2) NOT NULL,
   `year` int(5) NOT NULL,
   PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;