ALTER TABLE `glpi_plugin_resources` CHANGE `date` `date` DATE NULL default NULL;
ALTER TABLE `glpi_plugin_resources` CHANGE `begin_date` `begin_date` DATE NULL default NULL;
ALTER TABLE `glpi_plugin_resources` CHANGE `end_date` `end_date` DATE NULL default NULL;
ALTER TABLE `glpi_plugin_resources_tasks` CHANGE `begin_date` `begin_date` DATETIME NULL default NULL;
ALTER TABLE `glpi_plugin_resources_tasks` CHANGE `end_date` `end_date` DATETIME NULL default NULL;
UPDATE `glpi_plugin_resources` SET `date` = NULL WHERE `date` ='0000-00-00';
UPDATE `glpi_plugin_resources` SET `begin_date` = NULL WHERE `begin_date` ='0000-00-00';
UPDATE `glpi_plugin_resources` SET `end_date` = NULL WHERE `end_date` ='0000-00-00';
UPDATE `glpi_plugin_resources_tasks` SET `begin_date` = NULL WHERE `begin_date` ='0000-00-00 00:00:00';
UPDATE `glpi_plugin_resources_tasks` SET `end_date` = NULL WHERE `end_date` ='0000-00-00 00:00:00';

ALTER TABLE `glpi_plugin_resources` CHANGE `manager` `FK_users` int(4);

ALTER TABLE `glpi_plugin_resources` ADD INDEX `deleted` ( `deleted` );
ALTER TABLE `glpi_plugin_resources` ADD INDEX `is_template` ( `is_template` );
ALTER TABLE `glpi_plugin_resources` ADD INDEX `end_date` ( `end_date` );
ALTER TABLE `glpi_plugin_resources` ADD INDEX `leaving` ( `leaving` );
ALTER TABLE `glpi_plugin_resources` ADD INDEX `FK_users` ( `FK_users` );
ALTER TABLE `glpi_plugin_resources` ADD INDEX `recipient` ( `recipient` );
ALTER TABLE `glpi_plugin_resources` ADD INDEX `name` ( `name` );

ALTER TABLE `glpi_plugin_resources_needs` ADD INDEX `is_template` (`is_template`);

ALTER TABLE `glpi_plugin_resources_device` ADD INDEX `is_template` (`is_template`);

ALTER TABLE `glpi_plugin_resources_employee` ADD INDEX `FK_resources` (`FK_resources`);

ALTER TABLE `glpi_dropdown_plugin_resources_employer` ADD INDEX `FK_entities` (`FK_entities`);
ALTER TABLE `glpi_dropdown_plugin_resources_client` ADD INDEX `FK_entities` (`FK_entities`);
ALTER TABLE `glpi_dropdown_plugin_resources_type` ADD INDEX `FK_entities` (`FK_entities`);
ALTER TABLE `glpi_dropdown_plugin_resources_department` ADD INDEX `FK_entities` (`FK_entities`);

ALTER TABLE `glpi_plugin_resources_tasks` ADD INDEX `FK_resources` (`FK_resources`);
ALTER TABLE `glpi_plugin_resources_tasks` ADD INDEX `end_date` (`end_date`);
ALTER TABLE `glpi_plugin_resources_tasks` ADD INDEX `deleted` (`deleted`);
ALTER TABLE `glpi_plugin_resources_tasks` ADD INDEX `installed` (`installed`);
ALTER TABLE `glpi_plugin_resources_profiles` DROP COLUMN `interface`, DROP COLUMN `is_default`;
ALTER TABLE `glpi_dropdown_plugin_resources_tasks_type` ADD INDEX `FK_entities` (`FK_entities`);

ALTER TABLE `glpi_plugin_resources_device` ADD `comments` text AFTER `device_type`;

CREATE TABLE IF NOT EXISTS `glpi_plugin_resources_checklists` (
	`ID` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`FK_entities` int(11) NOT NULL default '0',
	`FK_resources` int(11) NOT NULL default '0',
	`FK_task` int(11) NOT NULL default '0',
	`type` tinyint(4) NOT NULL default '0',
	`checklist_type` int(11) NOT NULL default '0',
	`checked` int(11) NOT NULL default '0',
	`is_default` smallint(6) NOT NULL default '0',
	`address` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`rank` smallint(6) NOT NULL default '0',
	`comments` text,
	PRIMARY KEY  (`ID`),
	KEY `name` (`name`),
	KEY `FK_resources` (`FK_resources`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO glpi_plugin_resources_mailing VALUES ('3','checklists','1','1');

ALTER TABLE `glpi_plugin_resources_profiles` ADD `open_ticket` char(1) default NULL;