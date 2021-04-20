ALTER TABLE `glpi_plugin_sgbd`
  RENAME `glpi_plugin_databases_databases`;
ALTER TABLE `glpi_dropdown_plugin_sgbd_type`
  RENAME `glpi_plugin_databases_databasetypes`;
ALTER TABLE `glpi_dropdown_plugin_sgbd_category`
  RENAME `glpi_plugin_databases_databasecategories`;
ALTER TABLE `glpi_dropdown_plugin_sgbd_server_type`
  RENAME `glpi_plugin_databases_servertypes`;
ALTER TABLE `glpi_dropdown_plugin_sgbd_script_type`
  RENAME `glpi_plugin_databases_scripttypes`;
ALTER TABLE `glpi_plugin_sgbd_instances`
  RENAME `glpi_plugin_databases_instances`;
ALTER TABLE `glpi_plugin_sgbd_scripts`
  RENAME `glpi_plugin_databases_scripts`;
ALTER TABLE `glpi_plugin_sgbd_device`
  RENAME `glpi_plugin_databases_databases_items`;
ALTER TABLE `glpi_plugin_sgbd_profiles`
  RENAME `glpi_plugin_databases_profiles`;

ALTER TABLE `glpi_plugin_databases_databases`
  CHANGE `ID` `id` INT(11) NOT NULL AUTO_INCREMENT,
  CHANGE `name` `name` VARCHAR(255)
COLLATE utf8_unicode_ci DEFAULT NULL,
  CHANGE `FK_entities` `entities_id` INT(11) NOT NULL DEFAULT '0',
  CHANGE `recursive` `is_recursive` TINYINT(1) NOT NULL DEFAULT '0',
  CHANGE `category` `plugin_databases_databasecategories_id` INT(11) NOT NULL DEFAULT '0'
COMMENT 'RELATION to glpi_plugin_databases_databasecategories (id)',
  CHANGE `type` `plugin_databases_databasetypes_id` INT(11) NOT NULL DEFAULT '0'
COMMENT 'RELATION to glpi_plugin_databases_databasetypes (id)',
  CHANGE `FK_users` `users_id` INT(11) NOT NULL DEFAULT '0'
COMMENT 'RELATION to glpi_users (id)',
  CHANGE `FK_groups` `groups_id` INT(11) NOT NULL DEFAULT '0'
COMMENT 'RELATION to glpi_groups (id)',
  CHANGE `server` `plugin_databases_servertypes_id` INT(11) NOT NULL DEFAULT '0'
COMMENT 'RELATION to glpi_plugin_databases_servertypes (id)',
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
  ADD INDEX (`plugin_databases_databasecategories_id`),
  ADD INDEX (`plugin_databases_databasetypes_id`),
  ADD INDEX (`plugin_databases_servertypes_id`),
  ADD INDEX (`users_id`),
  ADD INDEX (`groups_id`),
  ADD INDEX (`suppliers_id`),
  ADD INDEX (`manufacturers_id`),
  ADD INDEX (`locations_id`),
  ADD INDEX (`date_mod`),
  ADD INDEX (`is_helpdesk_visible`),
  ADD INDEX (`is_deleted`);

ALTER TABLE `glpi_plugin_databases_databasetypes`
  CHANGE `ID` `id` INT(11) NOT NULL AUTO_INCREMENT,
  CHANGE `FK_entities` `entities_id` INT(11) NOT NULL DEFAULT '0',
  CHANGE `name` `name` VARCHAR(255)
COLLATE utf8_unicode_ci DEFAULT NULL,
  CHANGE `comments` `comment` TEXT COLLATE utf8_unicode_ci;

ALTER TABLE `glpi_plugin_databases_databasecategories`
  CHANGE `ID` `id` INT(11) NOT NULL AUTO_INCREMENT,
  CHANGE `FK_entities` `entities_id` INT(11) NOT NULL DEFAULT '0',
  CHANGE `name` `name` VARCHAR(255)
COLLATE utf8_unicode_ci DEFAULT NULL,
  CHANGE `comments` `comment` TEXT COLLATE utf8_unicode_ci;

ALTER TABLE `glpi_plugin_databases_servertypes`
  CHANGE `ID` `id` INT(11) NOT NULL AUTO_INCREMENT,
  CHANGE `name` `name` VARCHAR(255)
COLLATE utf8_unicode_ci DEFAULT NULL,
  CHANGE `comments` `comment` TEXT COLLATE utf8_unicode_ci;

ALTER TABLE `glpi_plugin_databases_scripttypes`
  CHANGE `ID` `id` INT(11) NOT NULL AUTO_INCREMENT,
  CHANGE `name` `name` VARCHAR(255)
COLLATE utf8_unicode_ci DEFAULT NULL,
  CHANGE `comments` `comment` TEXT COLLATE utf8_unicode_ci;

ALTER TABLE `glpi_plugin_databases_instances`
  CHANGE `ID` `id` INT(11) NOT NULL AUTO_INCREMENT,
  ADD `entities_id` INT(11) NOT NULL DEFAULT '0',
  ADD `is_recursive` TINYINT(1) NOT NULL DEFAULT '0',
  CHANGE `FK_sgbd` `plugin_databases_databases_id` INT(11) NOT NULL DEFAULT '0'
COMMENT 'RELATION to glpi_plugin_databases_databases (id)',
  CHANGE `name` `name` VARCHAR(255)
COLLATE utf8_unicode_ci DEFAULT NULL,
  CHANGE `path` `path` VARCHAR(255)
COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `comment` TEXT COLLATE utf8_unicode_ci,
  ADD INDEX (`name`),
  ADD INDEX (`plugin_databases_databases_id`);

ALTER TABLE `glpi_plugin_databases_scripts`
  CHANGE `ID` `id` INT(11) NOT NULL AUTO_INCREMENT,
  ADD `entities_id` INT(11) NOT NULL DEFAULT '0',
  ADD `is_recursive` TINYINT(1) NOT NULL DEFAULT '0',
  CHANGE `FK_sgbd` `plugin_databases_databases_id` INT(11) NOT NULL DEFAULT '0'
COMMENT 'RELATION to glpi_plugin_databases_databases (id)',
  CHANGE `type` `plugin_databases_scripttypes_id` INT(11) NOT NULL DEFAULT '0'
COMMENT 'RELATION to glpi_plugin_databases_scripttypes (id)',
  CHANGE `name` `name` VARCHAR(255)
COLLATE utf8_unicode_ci DEFAULT NULL,
  CHANGE `path` `path` VARCHAR(255)
COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `comment` TEXT COLLATE utf8_unicode_ci,
  ADD INDEX (`name`),
  ADD INDEX (`plugin_databases_databases_id`),
  ADD INDEX (`plugin_databases_scripttypes_id`);

ALTER TABLE `glpi_plugin_databases_databases_items`
  CHANGE `ID` `id` INT(11) NOT NULL AUTO_INCREMENT,
  CHANGE `FK_sgbd` `plugin_databases_databases_id` INT(11) NOT NULL DEFAULT '0',
  CHANGE `FK_device` `items_id` INT(11) NOT NULL DEFAULT '0'
COMMENT 'RELATION to various tables, according to itemtype (id)',
  CHANGE `device_type` `itemtype` VARCHAR(100)
COLLATE utf8_unicode_ci NOT NULL
COMMENT 'see .class.php file',
  DROP INDEX `FK_sgbd`,
  DROP INDEX `FK_sgbd_2`,
  DROP INDEX `FK_device`,
  ADD UNIQUE `unicity` (`plugin_databases_databases_id`, `itemtype`, `items_id`),
  ADD INDEX `FK_device` (`items_id`, `itemtype`),
  ADD INDEX `item` (`itemtype`, `items_id`);

ALTER TABLE `glpi_plugin_databases_profiles`
  CHANGE `ID` `id` INT(11) NOT NULL AUTO_INCREMENT,
  ADD `profiles_id` INT(11) NOT NULL DEFAULT '0'
COMMENT 'RELATION to glpi_profiles (id)',
  CHANGE `sgbd` `databases` CHAR(1)
COLLATE utf8_unicode_ci DEFAULT NULL,
  CHANGE `open_ticket` `open_ticket` CHAR(1)
COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD INDEX (`profiles_id`);