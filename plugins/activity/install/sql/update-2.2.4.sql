ALTER TABLE `glpi_plugin_activity_holidaytypes` ADD `is_holiday_counter` tinyint(1) default 0;
ALTER TABLE `glpi_plugin_activity_holidays` ADD `plugin_activity_holidayperiods_id` int(4) default 0;
