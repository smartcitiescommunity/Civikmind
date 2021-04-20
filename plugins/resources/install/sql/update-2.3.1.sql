DROP TABLE IF EXISTS `glpi_plugin_resources_accessprofiles`;
CREATE TABLE `glpi_plugin_resources_accessprofiles` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `code` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `is_recursive` (`is_recursive`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_resources_changes`;
CREATE TABLE `glpi_plugin_resources_resources_changes` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `actions_id` tinyint(1) NOT NULL DEFAULT '0',
   `itilcategories_id` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `entities_id` (`entities_id`),
   KEY `itilcategories_id` (`itilcategories_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `glpi_plugin_resources_resourcebadges`;
CREATE TABLE `glpi_plugin_resources_resourcebadges` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `plugin_metademands_metademands_id` tinyint(1) NOT NULL DEFAULT '0',
   PRIMARY KEY  (`id`),
   KEY `entities_id` (`entities_id`),
   KEY `plugin_metademands_metademands_id` (`plugin_metademands_metademands_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_resourcehabilitations`;
CREATE TABLE `glpi_plugin_resources_resourcehabilitations` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `action` tinyint(1) NOT NULL DEFAULT '0',
   `plugin_metademands_metademands_id` tinyint(1) NOT NULL DEFAULT '0',
   PRIMARY KEY  (`id`),
   KEY `entities_id` (`entities_id`),
   KEY `plugin_metademands_metademands_id` (`plugin_metademands_metademands_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE glpi_plugin_resources_resources ADD `plugin_resources_accessprofiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_accessprofiles (id)';
ALTER TABLE glpi_plugin_resources_resources ADD `users_id_sales` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)';
ALTER TABLE glpi_plugin_resources_resources ADD `date_declaration_leaving` datetime default NULL;

INSERT INTO `glpi_notificationtemplates` (name, itemtype)
VALUES('Resources list of commercial manager', 'PluginResourcesResource');