ALTER TABLE `glpi_plugin_resources_resources` 
   ADD `plugin_resources_resourcestates_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_resourcestates (id)',
   ADD `picture` varchar(100) collate utf8_unicode_ci default NULL,
   ADD INDEX (`plugin_resources_resourcestates_id`);

CREATE TABLE `glpi_plugin_resources_resourcestates` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
