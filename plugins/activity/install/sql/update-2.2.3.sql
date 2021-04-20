ALTER TABLE `glpi_plugin_activity_activities` CHANGE `begin_date` `begin` DATETIME NULL default NULL;
ALTER TABLE `glpi_plugin_activity_activities` CHANGE `end_date` `end` DATETIME NULL default NULL;
ALTER TABLE `glpi_plugin_activity_holidays` CHANGE `begin_date` `begin` DATETIME NULL default NULL;
ALTER TABLE `glpi_plugin_activity_holidays` CHANGE `end_date` `end` DATETIME NULL default NULL;
ALTER TABLE `glpi_plugin_activity_holidayperiods` CHANGE `begin_date` `begin` DATE NULL default NULL;
ALTER TABLE `glpi_plugin_activity_holidayperiods` CHANGE `end_date` `end` DATE NULL default NULL;