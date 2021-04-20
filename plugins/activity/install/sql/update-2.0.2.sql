ALTER TABLE glpi_plugin_activity_holidaytypes ADD `is_holiday` tinyint(1) default 1;
ALTER TABLE glpi_plugin_activity_holidaytypes ADD `is_sickness` tinyint(1) default 0;
ALTER TABLE glpi_plugin_activity_holidaytypes ADD `is_part_time` tinyint(1) default 0;