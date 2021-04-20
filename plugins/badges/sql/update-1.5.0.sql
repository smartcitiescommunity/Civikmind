ALTER TABLE `glpi_plugin_badges` CHANGE `date_affect` `date_affect` DATE NULL default NULL;
UPDATE `glpi_plugin_badges` SET `date_affect` = NULL WHERE `date_affect` ='0000-00-00';
ALTER TABLE `glpi_plugin_badges` CHANGE `date_expiration` `date_expiration` DATE NULL default NULL;
UPDATE `glpi_plugin_badges` SET `date_expiration` = NULL WHERE `date_expiration` ='0000-00-00';

ALTER TABLE `glpi_plugin_badges` ADD INDEX `name` (`name`);
ALTER TABLE `glpi_plugin_badges` ADD INDEX `deleted` (`deleted`);
ALTER TABLE `glpi_plugin_badges` ADD INDEX `FK_entities` (`FK_entities`);
ALTER TABLE `glpi_plugin_badges` ADD INDEX `date_expiration` (`date_expiration`);
ALTER TABLE `glpi_plugin_badges` ADD INDEX `state` (`state`);

ALTER TABLE `glpi_dropdown_plugin_badges_type` ADD INDEX `FK_entities` (`FK_entities`);

ALTER TABLE `glpi_plugin_badges_profiles` ADD INDEX `name` (`name`);

ALTER TABLE `glpi_plugin_badges_default` ADD INDEX `status` (`status`);

ALTER TABLE `glpi_plugin_badges_profiles` DROP COLUMN `interface` , DROP COLUMN `is_default`;

ALTER TABLE `glpi_plugin_badges` ADD `FK_users` int(4) AFTER `state`;
ALTER TABLE `glpi_plugin_badges_profiles` ADD `open_ticket` char(1) default NULL;