ALTER TABLE glpi_plugin_resources_configs ADD `security_compliance` tinyint(1) NOT NULL default '0';

ALTER TABLE glpi_plugin_resources_clients ADD `security_and` tinyint(1) NOT NULL default '0';
ALTER TABLE glpi_plugin_resources_clients ADD `security_fifour` tinyint(1) NOT NULL default '0';
ALTER TABLE glpi_plugin_resources_clients ADD `security_gisf` tinyint(1) NOT NULL default '0';
ALTER TABLE glpi_plugin_resources_clients ADD `security_cfi` tinyint(1) NOT NULL default '0';