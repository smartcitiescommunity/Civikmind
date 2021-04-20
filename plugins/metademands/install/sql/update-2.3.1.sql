ALTER TABLE glpi_plugin_metademands_fields ADD `color` varchar(255) default NULL;
ALTER TABLE glpi_plugin_metademands_metademands ADD `is_active` tinyint(1) NOT NULL DEFAULT '1';
