DROP TABLE IF EXISTS `glpi_plugin_resources_configs`;
CREATE TABLE `glpi_plugin_resources_configs` (
  `id` int(11) NOT NULL auto_increment,
  `security_display` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_resources_configs` VALUES(1, 0);

ALTER TABLE glpi_plugin_resources_resources ADD `sensitize_security` tinyint(1) NOT NULL default '0';
ALTER TABLE glpi_plugin_resources_resources ADD `read_chart` tinyint(1) NOT NULL default '0';
ALTER TABLE `glpi_plugin_resources_resources` CHANGE `plugin_resources_accessprofiles_id` `plugin_resources_habilitations_id` INT(11) NOT NULL DEFAULT '0' COMMENT 'RELATION to glpi_plugin_resources_habilitations (id)';
ALTER TABLE `glpi_plugin_resources_clients` ADD `security_compliance` tinyint(1) NOT NULL DEFAULT '0';

DROP TABLE IF EXISTS `glpi_plugin_resources_confighabilitations`;
ALTER TABLE `glpi_plugin_resources_resourcehabilitations` RENAME TO `glpi_plugin_resources_confighabilitations`;

DROP TABLE IF EXISTS `glpi_plugin_resources_habilitations`;
CREATE TABLE `glpi_plugin_resources_habilitations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) NOT NULL DEFAULT '0',
  `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `plugin_resources_habilitations_id` int(11) NOT NULL default '0',
  `completename` text COLLATE utf8_unicode_ci,
  `comment` text COLLATE utf8_unicode_ci,
  `level` int(11) NOT NULL DEFAULT '0',
  `ancestors_cache` longtext COLLATE utf8_unicode_ci,
  `sons_cache` longtext COLLATE utf8_unicode_ci,
  `allow_resource_creation` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`),
  KEY `plugin_resources_habilitations_id` (`plugin_resources_habilitations_id`),
  KEY `entities_id` (`entities_id`),
  KEY `is_recursive` (`is_recursive`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_resourcehabilitations`;
CREATE TABLE `glpi_plugin_resources_resourcehabilitations` (
  `id` int(11) NOT NULL auto_increment,
  `plugin_resources_resources_id` int(11) NOT NULL default '0',
  `plugin_resources_habilitations_id` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY `glpi_plugin_resources_resources_id` (`plugin_resources_resources_id`),
  KEY `glpi_plugin_resources_habilitations_id` (`plugin_resources_habilitations_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;