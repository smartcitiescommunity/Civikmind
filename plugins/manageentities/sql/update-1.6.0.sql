ALTER TABLE `glpi_plugin_manageentity_contracts` RENAME `glpi_plugin_manageentities_contracts`;
INSERT INTO glpi_documents_items (documents_id,items_id,itemtype) SELECT FK_documents, FK_entities, 'Entity' FROM glpi_plugin_manageentity_documents;
DROP TABLE `glpi_plugin_manageentity_documents`;
ALTER TABLE `glpi_plugin_manageentity_contacts` RENAME `glpi_plugin_manageentities_contacts`;
ALTER TABLE `glpi_plugin_manageentity_profiles` RENAME `glpi_plugin_manageentities_profiles`;
ALTER TABLE `glpi_plugin_manageentity_preference` RENAME `glpi_plugin_manageentities_preferences`;
ALTER TABLE `glpi_plugin_manageentity_config` RENAME `glpi_plugin_manageentities_configs`;
ALTER TABLE `glpi_dropdown_plugin_manageentity_critype` RENAME `glpi_plugin_manageentities_critypes`;
ALTER TABLE `glpi_plugin_manageentity_criprice` RENAME `glpi_plugin_manageentities_criprices`;
ALTER TABLE `glpi_plugin_manageentity_dayforcontract` RENAME `glpi_plugin_manageentities_contractdays`;
ALTER TABLE `glpi_plugin_manageentity_critechnicians` RENAME `glpi_plugin_manageentities_critechnicians`;
ALTER TABLE `glpi_plugin_manageentity_cridetails` RENAME `glpi_plugin_manageentities_cridetails`;

ALTER TABLE `glpi_plugin_manageentities_contracts` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_contracts` `contracts_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_contracts (id)',
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `isdefault` `is_default` tinyint(1) NOT NULL default '0',
   ADD UNIQUE `unicity` (`contracts_id`,`entities_id`),
   ADD INDEX `entities_id` (`entities_id`);

ALTER TABLE `glpi_plugin_manageentities_contacts` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_contacts` `contacts_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_contacts (id)',
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `isdefault` `is_default` tinyint(1) NOT NULL default '0',
   ADD UNIQUE `unicity` (`contacts_id`,`entities_id`),
   ADD INDEX `entities_id` (`entities_id`);

ALTER TABLE `glpi_plugin_manageentities_profiles` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   ADD `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
   CHANGE `manageentity` `manageentities` char(1) collate utf8_unicode_ci default NULL,
   CHANGE `cri` `cri_create` char(1) collate utf8_unicode_ci default NULL,
   ADD INDEX (`profiles_id`);

ALTER TABLE `glpi_plugin_manageentities_preferences` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `user_id` `users_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
   CHANGE `show` `show_on_load` int(11) NOT NULL default '0';

ALTER TABLE `glpi_plugin_manageentities_configs` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `backup` `backup` int(11) NOT NULL default '0',
   CHANGE `rubrique` `documentcategories_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_documentcategories (id)',
   CHANGE `hourbyday` `hourbyday` int(11) NOT NULL default '0',
   ADD INDEX `documentcategories_id` (`documentcategories_id`);

ALTER TABLE `glpi_plugin_manageentities_critypes` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `comments` `comment` text collate utf8_unicode_ci;

ALTER TABLE `glpi_plugin_manageentities_criprices` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `FK_typecri` `plugin_manageentities_critypes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_manageentities_critypes (id)',
   ADD INDEX `entities_id` (`entities_id`),
   ADD INDEX `plugin_manageentities_critypes_id` (`plugin_manageentities_critypes_id`);

ALTER TABLE `glpi_plugin_manageentities_contractdays` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `FK_typecri` `plugin_manageentities_critypes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_manageentities_critypes (id)',
   CHANGE `FK_contracts` `contracts_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_contracts (id)',
   ADD INDEX `entities_id` (`entities_id`),
   ADD INDEX `plugin_manageentities_critypes_id` (`plugin_manageentities_critypes_id`),
   ADD INDEX `contracts_id` (`contracts_id`);


ALTER TABLE `glpi_plugin_manageentities_critechnicians` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_ticket` `tickets_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_tickets (id)',
   CHANGE `FK_users` `users_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
   ADD INDEX `tickets_id` (`tickets_id`),
   ADD INDEX `users_id` (`users_id`);

ALTER TABLE `glpi_plugin_manageentities_cridetails` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `FK_doc` `documents_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_documents (id)',
   CHANGE `type_cri` `plugin_manageentities_critypes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_manageentities_critypes (id)',
   CHANGE `withcontract` `withcontract` int(11) NOT NULL default '0',
   CHANGE `FK_contracts` `contracts_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_contracts (id)',
   CHANGE `technicians` `technicians` varchar(255) collate utf8_unicode_ci default NULL,
   ADD INDEX `documents_id` (`documents_id`),
   ADD INDEX `plugin_manageentities_critypes_id` (`plugin_manageentities_critypes_id`);