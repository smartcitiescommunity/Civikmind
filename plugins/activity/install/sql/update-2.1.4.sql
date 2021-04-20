ALTER TABLE glpi_plugin_activity_options ADD `use_groupmanager` tinyint(1) default 0;
ALTER TABLE glpi_plugin_activity_options ADD `default_validation_percent` int(11) NOT NULL DEFAULT '0';
ALTER TABLE glpi_plugin_activity_holidays ADD `validation_percent` int(11) NOT NULL DEFAULT '0';
ALTER TABLE glpi_plugin_activity_holidays CHANGE `status` `global_validation` int(11) NOT NULL DEFAULT '1';