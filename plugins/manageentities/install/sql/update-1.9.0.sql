ALTER TABLE `glpi_plugin_manageentities_contracts`
   ADD `management` tinyint(1) NOT NULL default '0' COMMENT 'for the management mode (quarterly or annual or not)',
   ADD `contract_type` tinyint(1) NOT NULL default '0' COMMENT 'for the contract type (hour, intervention, unlimited or not)',
   ADD `date_signature` date default NULL,
   ADD `date_renewal` date default NULL,
   ADD INDEX `contracts_id` (`contracts_id`);

ALTER TABLE `glpi_plugin_manageentities_contacts`
   ADD INDEX `contacts_id` (`contacts_id`);

ALTER TABLE `glpi_plugin_manageentities_preferences`
   ADD INDEX `users_id` (`users_id`);

ALTER TABLE `glpi_plugin_manageentities_configs`
   ADD `useprice` tinyint(1) NOT NULL default '1' COMMENT 'default for yes',
   ADD `hourorday` tinyint(1) NOT NULL default '0' COMMENT 'default for day',
   ADD `needvalidationforcri` tinyint(1) NOT NULL default '0' COMMENT 'if only CRI with validated ticket are taking into account for consumption calculation',
   ADD `use_publictask` tinyint(1) NOT NULL default '0' COMMENT 'default for no';

UPDATE `glpi_plugin_manageentities_configs`
SET `hourorday` = '0',`hourbyday` = '8',`needvalidationforcri` = '0'
WHERE `id`='1';

ALTER TABLE `glpi_plugin_manageentities_contractdays`
   ADD `name` varchar(255) collate utf8_unicode_ci default NULL,
   ADD `plugin_manageentities_contractstates_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_manageentities_contractstates (id)',
   ADD `begin_date` date default NULL,
   ADD `end_date` date default NULL,
   ADD `report` decimal(20,2) default '0.00',
   ADD INDEX `plugin_manageentities_contractstates_id` (`plugin_manageentities_contractstates_id`);

ALTER TABLE `glpi_plugin_manageentities_cridetails`
   ADD `tickets_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_tickets (id)',
   ADD INDEX `entities_id` (`entities_id`),
   ADD INDEX `tickets_id` (`tickets_id`);

DROP TABLE IF EXISTS `glpi_plugin_manageentities_contractstates`;
CREATE TABLE `glpi_plugin_manageentities_contractstates` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `is_active` tinyint(1) NOT NULL default '0',
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `is_active` (`is_active`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_manageentities_taskcategories`;
CREATE TABLE `glpi_plugin_manageentities_taskcategories` (
   `id` int(11) NOT NULL auto_increment,
   `taskcategories_id` int(11) NOT NULL default '0' COMMENT 'RELATION to  glpi_taskcategories (id)',
   `is_usedforcount` tinyint(1) NOT NULL default '0',
   PRIMARY KEY  (`id`),
   KEY `taskcategories_id` (`taskcategories_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;