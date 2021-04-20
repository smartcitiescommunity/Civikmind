DROP TABLE IF EXISTS `glpi_plugin_manageentities_contracts`;
CREATE TABLE `glpi_plugin_manageentities_contracts` (
   `id` int(11) NOT NULL auto_increment,
   `contracts_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_contracts (id)',
   `entities_id` int(11) NOT NULL default '0',
   `is_default` tinyint(1) NOT NULL default '0',
   PRIMARY KEY  (`id`),
   UNIQUE KEY `unicity` (`contracts_id`,`entities_id`),
   KEY `entities_id` (`entities_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_manageentities_contacts`;
CREATE TABLE `glpi_plugin_manageentities_contacts` (
   `id` int(11) NOT NULL auto_increment,
   `contacts_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_contacts (id)',
   `entities_id` int(11) NOT NULL default '0',
   `is_default` tinyint(1) NOT NULL default '0',
   PRIMARY KEY  (`id`),
   UNIQUE KEY `unicity` (`contacts_id`,`entities_id`),
   KEY `entities_id` (`entities_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_manageentities_profiles`;
CREATE TABLE `glpi_plugin_manageentities_profiles` (
   `id` int(11) NOT NULL auto_increment,
	`profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
	`manageentities` char(1) collate utf8_unicode_ci default NULL,
	`cri_create` char(1) collate utf8_unicode_ci default NULL,
	PRIMARY KEY  (`id`),
	KEY `profiles_id` (`profiles_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_manageentities_preferences`;
CREATE TABLE `glpi_plugin_manageentities_preferences` (
	`id` int(11) NOT NULL auto_increment,
	`users_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
	`show_on_load` int(11) NOT NULL default '0',
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_manageentities_configs`;
CREATE TABLE `glpi_plugin_manageentities_configs` (
	`id` int(11) NOT NULL auto_increment,
	`backup` int(11) NOT NULL default '0',
	`documentcategories_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_documentcategories (id)',
	`hourbyday` int(11) NOT NULL default '0',
	PRIMARY KEY  (`id`),
	KEY `documentcategories_id` (`documentcategories_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_manageentities_configs` (`id`,`backup`,`documentcategories_id`,`hourbyday`) VALUES ('1', '0','-1','8');

DROP TABLE IF EXISTS `glpi_plugin_manageentities_critypes`;
CREATE TABLE `glpi_plugin_manageentities_critypes` (
	`id` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_manageentities_criprices`;
CREATE TABLE `glpi_plugin_manageentities_criprices` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`plugin_manageentities_critypes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_manageentities_critypes (id)',
	`price` decimal(20,4) NOT NULL default '0.0000',
	PRIMARY KEY  (`id`),
	KEY `entities_id` (`entities_id`),
	KEY `plugin_manageentities_critypes_id` (`plugin_manageentities_critypes_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_manageentities_contractdays`;
CREATE TABLE `glpi_plugin_manageentities_contractdays` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`plugin_manageentities_critypes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_manageentities_critypes (id)',
	`contracts_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_contracts (id)',
	`nbday` decimal(20,2) default '0.00',
	PRIMARY KEY  (`id`),
	KEY `entities_id` (`entities_id`),
	KEY `contracts_id` (`contracts_id`),
	KEY `plugin_manageentities_critypes_id` (`plugin_manageentities_critypes_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_manageentities_critechnicians`;
CREATE TABLE `glpi_plugin_manageentities_critechnicians` (
	`id` int(11) NOT NULL auto_increment,
	`tickets_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_tickets (id)',
	`users_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
	PRIMARY KEY  (`id`),
	KEY `tickets_id` (`tickets_id`),
	KEY `users_id` (`users_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_manageentities_cridetails`;
CREATE TABLE `glpi_plugin_manageentities_cridetails` (
	`id` int(11) NOT NULL auto_increment,
	`entities_id` int(11) NOT NULL default '0',
	`date` date default NULL,
	`documents_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_documents (id)',
	`plugin_manageentities_critypes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_manageentities_critypes (id)',
	`withcontract` int(11) NOT NULL default '0',
	`contracts_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_contracts (id)',
	`realtime` decimal(20,2) default '0.00',
	`technicians` varchar(255) collate utf8_unicode_ci default NULL,
	PRIMARY KEY  (`id`),
	KEY `documents_id` (`documents_id`),
	KEY `plugin_manageentities_critypes_id` (`plugin_manageentities_critypes_id`),
	KEY `contracts_id` (`contracts_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;