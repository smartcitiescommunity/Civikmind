ALTER TABLE `glpi_plugin_activity_options` ADD `use_project` tinyint(11) DEFAULT '0';
ALTER TABLE `glpi_plugin_activity_options` ADD `is_cra_default_project` tinyint(11) DEFAULT '0';

DROP TABLE IF EXISTS `glpi_plugin_activity_projecttasks`;
CREATE TABLE `glpi_plugin_activity_projecttasks` (
   `id` int(11) NOT NULL auto_increment,
   `is_oncra` tinyint(1) default '1',
   `projecttasks_id` int(11) NOT NULL,
   PRIMARY KEY  (`id`),
   FOREIGN KEY (`projecttasks_id`) REFERENCES glpi_projecttasks(id),
   KEY `is_oncra` (`is_oncra`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;