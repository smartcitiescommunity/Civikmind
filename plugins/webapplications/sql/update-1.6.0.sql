DROP TABLE IF EXISTS `glpi_dropdown_plugin_appweb_protocol`;

ALTER TABLE `glpi_plugin_appweb`
  RENAME `glpi_plugin_webapplications_webapplications`;
ALTER TABLE `glpi_dropdown_plugin_appweb_type`
  RENAME `glpi_plugin_webapplications_webapplicationtypes`;
ALTER TABLE `glpi_dropdown_plugin_appweb_server_type`
  RENAME `glpi_plugin_webapplications_webapplicationservertypes`;
ALTER TABLE `glpi_dropdown_plugin_appweb_technic`
  RENAME `glpi_plugin_webapplications_webapplicationtechnics`;
ALTER TABLE `glpi_plugin_appweb_device`
  RENAME `glpi_plugin_webapplications_webapplications_items`;
ALTER TABLE `glpi_plugin_appweb_profiles`
  RENAME `glpi_plugin_webapplications_profiles`;

UPDATE `glpi_plugin_webapplications_webapplications`
SET `FK_users` = '0'
WHERE `FK_users` IS NULL;

ALTER TABLE `glpi_plugin_webapplications_webapplications`
  CHANGE `ID` `id` INT(11) NOT NULL AUTO_INCREMENT,
  CHANGE `FK_entities` `entities_id` INT(11) NOT NULL DEFAULT '0',
  CHANGE `recursive` `is_recursive` TINYINT(1) NOT NULL DEFAULT '0',
  CHANGE `name` `name` VARCHAR(255)
COLLATE utf8_unicode_ci DEFAULT NULL,
  CHANGE `address` `address` VARCHAR(255)
COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `backoffice` VARCHAR(255)
COLLATE utf8_unicode_ci DEFAULT NULL,
  CHANGE `type` `plugin_webapplications_webapplicationtypes_id` INT(11) NOT NULL DEFAULT '0'
COMMENT 'RELATION to glpi_plugin_webapplications_webapplicationtypes (id)',
  CHANGE `server` `plugin_webapplications_webapplicationservertypes_id` INT(11) NOT NULL DEFAULT '0'
COMMENT 'RELATION to glpi_plugin_webapplications_webapplicationservertypes (id)',
  CHANGE `technic` `plugin_webapplications_webapplicationtechnics_id` INT(11) NOT NULL DEFAULT '0'
COMMENT 'RELATION to glpi_plugin_webapplications_webapplicationtechnics (id)',
  CHANGE `version` `version` VARCHAR(255)
COLLATE utf8_unicode_ci DEFAULT NULL,
  CHANGE `FK_users` `users_id` INT(11) DEFAULT '0'
COMMENT 'RELATION to glpi_users (id)',
  CHANGE `FK_groups` `groups_id` INT(11) NOT NULL DEFAULT '0'
COMMENT 'RELATION to glpi_groups (id)',
  CHANGE `FK_enterprise` `suppliers_id` INT(11) NOT NULL DEFAULT '0'
COMMENT 'RELATION to glpi_suppliers (id)',
  CHANGE `FK_glpi_enterprise` `manufacturers_id` INT(11) NOT NULL DEFAULT '0'
COMMENT 'RELATION to glpi_manufacturers (id)',
  CHANGE `location` `locations_id` INT(11) NOT NULL DEFAULT '0'
COMMENT 'RELATION to glpi_locations (id)',
  CHANGE `helpdesk_visible` `is_helpdesk_visible` INT(11) NOT NULL DEFAULT '1',
  CHANGE `notes` `notepad` LONGTEXT COLLATE utf8_unicode_ci,
  CHANGE `comment` `comment` TEXT COLLATE utf8_unicode_ci,
  CHANGE `deleted` `is_deleted` TINYINT(1) NOT NULL DEFAULT '0',
  ADD INDEX (`name`),
  ADD INDEX (`entities_id`),
  ADD INDEX (`plugin_webapplications_webapplicationtypes_id`),
  ADD INDEX (`plugin_webapplications_webapplicationservertypes_id`),
  ADD INDEX (`plugin_webapplications_webapplicationtechnics_id`),
  ADD INDEX (`users_id`),
  ADD INDEX (`groups_id`),
  ADD INDEX (`suppliers_id`),
  ADD INDEX (`manufacturers_id`),
  ADD INDEX (`locations_id`),
  ADD INDEX (`date_mod`),
  ADD INDEX (`is_helpdesk_visible`),
  ADD INDEX (`is_deleted`);

ALTER TABLE `glpi_plugin_webapplications_webapplicationtypes`
  CHANGE `ID` `id` INT(11) NOT NULL AUTO_INCREMENT,
  CHANGE `FK_entities` `entities_id` INT(11) NOT NULL DEFAULT '0',
  CHANGE `name` `name` VARCHAR(255)
COLLATE utf8_unicode_ci DEFAULT NULL,
  CHANGE `comments` `comment` TEXT COLLATE utf8_unicode_ci;

ALTER TABLE `glpi_plugin_webapplications_webapplicationservertypes`
  CHANGE `ID` `id` INT(11) NOT NULL AUTO_INCREMENT,
  CHANGE `name` `name` VARCHAR(255)
COLLATE utf8_unicode_ci DEFAULT NULL,
  CHANGE `comments` `comment` TEXT COLLATE utf8_unicode_ci;

ALTER TABLE `glpi_plugin_webapplications_webapplicationtechnics`
  CHANGE `ID` `id` INT(11) NOT NULL AUTO_INCREMENT,
  CHANGE `name` `name` VARCHAR(255)
COLLATE utf8_unicode_ci DEFAULT NULL,
  CHANGE `comments` `comment` TEXT COLLATE utf8_unicode_ci;

ALTER TABLE `glpi_plugin_webapplications_webapplications_items`
  CHANGE `ID` `id` INT(11) NOT NULL AUTO_INCREMENT,
  CHANGE `FK_appweb` `plugin_webapplications_webapplications_id` INT(11) NOT NULL DEFAULT '0',
  CHANGE `FK_device` `items_id` INT(11) NOT NULL DEFAULT '0'
COMMENT 'RELATION to various tables, according to itemtype (id)',
  CHANGE `device_type` `itemtype` VARCHAR(100)
COLLATE utf8_unicode_ci NOT NULL
COMMENT 'see .class.php file',
  DROP INDEX `FK_appweb_2`,
  DROP INDEX `FK_device`,
  ADD UNIQUE `unicity` (`plugin_webapplications_webapplications_id`, `itemtype`, `items_id`),
  ADD INDEX `FK_device` (`items_id`, `itemtype`),
  ADD INDEX `item` (`itemtype`, `items_id`);

ALTER TABLE `glpi_plugin_webapplications_profiles`
  CHANGE `ID` `id` INT(11) NOT NULL AUTO_INCREMENT,
  ADD `profiles_id` INT(11) NOT NULL DEFAULT '0'
COMMENT 'RELATION to glpi_profiles (id)',
  CHANGE `appweb` `webapplications` CHAR(1)
COLLATE utf8_unicode_ci DEFAULT NULL,
  CHANGE `open_ticket` `open_ticket` CHAR(1)
COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD INDEX (`profiles_id`);
