ALTER TABLE `glpi_plugin_activity` CHANGE `begin_date` `begin_date` DATETIME NULL default NULL;
ALTER TABLE `glpi_plugin_activity` CHANGE `end_date` `end_date` DATETIME NULL default NULL;
UPDATE `glpi_plugin_activity` SET `begin_date` = NULL WHERE `begin_date` ='0000-00-00 00:00:00';
UPDATE `glpi_plugin_activity` SET `end_date` = NULL WHERE `end_date` ='0000-00-00 00:00:00';

ALTER TABLE `glpi_plugin_activity` ADD INDEX `tech_num` (`tech_num`);
ALTER TABLE `glpi_plugin_activity` ADD INDEX `end_date` (`end_date`);
ALTER TABLE `glpi_plugin_activity` ADD INDEX `deleted` (`deleted`);
ALTER TABLE `glpi_plugin_activity` ADD INDEX `begin_date` (`begin_date`);
ALTER TABLE `glpi_plugin_activity` ADD INDEX `type` (`type`);
ALTER TABLE `glpi_plugin_activity` ADD INDEX `FK_entities` (`FK_entities`);

ALTER TABLE `glpi_dropdown_plugin_activity_type` ADD INDEX `FK_profiles` (`FK_profiles`);