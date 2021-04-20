DROP TABLE IF EXISTS `glpi_plugin_activity_planningexternalevents`;
CREATE TABLE `glpi_plugin_activity_planningexternalevents` (
   `id` int(11) NOT NULL auto_increment,
   `is_oncra` tinyint(1) default '1',
   `planningexternalevents_id` int(11) NOT NULL,
   `actiontime` int(11) NOT NULL DEFAULT '0',
   PRIMARY KEY  (`id`),
   KEY `is_oncra` (`is_oncra`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `glpi_plugin_activity_holidays` CHANGE `date_mod` `date_mod` timestamp NULL DEFAULT NULL;
ALTER TABLE `glpi_plugin_activity_holidays` CHANGE `begin` `begin` timestamp NULL DEFAULT NULL;
ALTER TABLE `glpi_plugin_activity_holidays` CHANGE `end` `end` timestamp NULL DEFAULT NULL;

ALTER TABLE `glpi_plugin_activity_holidayvalidations` CHANGE `submission_date` `submission_date` timestamp NULL DEFAULT NULL;
ALTER TABLE `glpi_plugin_activity_holidayvalidations` CHANGE `validation_date` `validation_date` timestamp NULL DEFAULT NULL;

ALTER TABLE `glpi_plugin_activity_holidaycounts` CHANGE `date_mod` `date_mod` timestamp NULL DEFAULT NULL;

ALTER TABLE `glpi_plugin_activity_snapshots` CHANGE `date` `date` timestamp NULL DEFAULT NULL;

ALTER TABLE `glpi_plugin_activity_tickettasks` DROP INDEX `tickettasks_id`;
ALTER TABLE `glpi_plugin_activity_projecttasks` DROP INDEX `projecttasks_id`;