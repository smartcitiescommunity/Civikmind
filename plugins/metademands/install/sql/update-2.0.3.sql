ALTER TABLE glpi_plugin_metademands_fields ADD `comment` varchar(255) default NULL;
ALTER TABLE glpi_plugin_metademands_configs ADD `enable_application_environment` tinyint(1) default 0;
ALTER TABLE glpi_plugin_metademands_configs ADD `parent_ticket_tag` varchar(255) default NULL;
ALTER TABLE glpi_plugin_metademands_configs ADD `son_ticket_tag` varchar(255) default NULL;
ALTER TABLE glpi_plugin_metademands_configs ADD `show_requester_informations` tinyint(1) default 0;
ALTER TABLE glpi_plugin_metademands_fields ADD `order` int(11) default 0;
ALTER TABLE glpi_plugin_metademands_fields ADD INDEX `order` (`order`);
ALTER TABLE glpi_plugin_metademands_fields ADD `plugin_metademands_fields_id` int(11) default 0;
ALTER TABLE glpi_plugin_metademands_fields ADD INDEX `plugin_metademands_fields_id` (`plugin_metademands_fields_id`);
ALTER TABLE glpi_plugin_metademands_fields ADD `fields_link` int(11) default 0;
ALTER TABLE glpi_plugin_metademands_fields ADD INDEX `fields_link` (`fields_link`);

-- ------------------------------------------------------------
--
-- Structure de la table 'glpi_plugin_metademands_metademands_resources'
-- 
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_metademands_metademands_resources`;
CREATE TABLE `glpi_plugin_metademands_metademands_resources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) NOT NULL default '0',
  `plugin_resources_contracttypes_id` int(11) NOT NULL default '0',
  `plugin_metademands_metademands_id` int(11) NOT NULL default '0',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`plugin_metademands_metademands_id`) REFERENCES glpi_plugin_metademands_metademands(id),
  FOREIGN KEY (`plugin_resources_contracttypes_id`) REFERENCES glpi_plugin_resources_contracttypes(id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;