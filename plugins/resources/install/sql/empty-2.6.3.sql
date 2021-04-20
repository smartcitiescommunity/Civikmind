DROP TABLE IF EXISTS `glpi_plugin_resources_resources`;
CREATE TABLE `glpi_plugin_resources_resources` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `firstname` varchar(255) collate utf8_unicode_ci default NULL,
   `plugin_resources_contracttypes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_contracttypes (id)',
   `users_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
   `users_id_sales` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
   `users_id_recipient` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
   `date_declaration` date default NULL,
   `date_begin` date default NULL,
   `date_end` date default NULL,
   `quota` decimal(10,4) NOT NULL default '1.00',
   `plugin_resources_departments_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_departments (id)',
   `plugin_resources_resourcestates_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_resourcestates (id)',
   `plugin_resources_resourcesituations_id` int(11) NOT NULL default '0',
   `plugin_resources_contractnatures_id` int(11) NOT NULL default '0',
   `plugin_resources_ranks_id` int(11) NOT NULL default '0',
   `plugin_resources_resourcespecialities_id` int(11) NOT NULL default '0',
   `locations_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_locations (id)',
   `is_leaving` int(11) NOT NULL default '0',
   `plugin_resources_leavingreasons_id` int(11) NOT NULL default '0',
   `date_declaration_leaving` datetime default NULL,
   `users_id_recipient_leaving` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
   `picture` varchar(100) collate utf8_unicode_ci default NULL,
   `is_helpdesk_visible` int(11) NOT NULL default '1',
   `date_mod` datetime default NULL,
   `comment` text collate utf8_unicode_ci,
   `is_template` tinyint(1) NOT NULL default '0',
   `template_name` varchar(255) collate utf8_unicode_ci default NULL,
   `is_deleted` tinyint(1) NOT NULL default '0',
   `sensitize_security` tinyint(1) NOT NULL default '0',
   `read_chart` tinyint(1) NOT NULL default '0',
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `is_recursive` (`is_recursive`),
   KEY `users_id` (`users_id`),
   KEY `users_id_sales` (`users_id_sales`),
   KEY `users_id_recipient` (`users_id_recipient`),
   KEY `locations_id` (`locations_id`),
   KEY `is_leaving` (`is_leaving`),
   KEY `users_id_recipient_leaving` (`users_id_recipient_leaving`),
   KEY `date_mod` (`date_mod`),
   KEY `is_helpdesk_visible` (`is_helpdesk_visible`),
   KEY `is_deleted` (`is_deleted`),
   KEY `is_template` (`is_template`),
   KEY `plugin_resources_contracttypes_id` (`plugin_resources_contracttypes_id`),
   KEY `plugin_resources_departments_id` (`plugin_resources_departments_id`),
   KEY `plugin_resources_resourcestates_id` (`plugin_resources_resourcestates_id`),
   KEY `plugin_resources_resourcesituations_id` (`plugin_resources_resourcesituations_id`),
   KEY `plugin_resources_contractnatures_id` (`plugin_resources_contractnatures_id`),
   KEY `plugin_resources_ranks_id` (`plugin_resources_ranks_id`),
   KEY `plugin_resources_resourcespecialities_id` (`plugin_resources_resourcespecialities_id`),
   KEY `plugin_resources_leavingreasons_id` (`plugin_resources_leavingreasons_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_resourcestates`;
CREATE TABLE `glpi_plugin_resources_resourcestates` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `is_recursive` (`is_recursive`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_choices`;
CREATE TABLE `glpi_plugin_resources_choices` (
   `id` int(11) NOT NULL auto_increment,
   `plugin_resources_resources_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_resources (id)',
   `plugin_resources_choiceitems_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_choiceitems (id)',
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `plugin_resources_resources_id` (`plugin_resources_resources_id`),
   KEY `plugin_resources_choiceitems_id` (`plugin_resources_choiceitems_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_choiceitems`;
CREATE TABLE `glpi_plugin_resources_choiceitems` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   `plugin_resources_choiceitems_id` int(11) NOT NULL DEFAULT '0',
   `completename` text COLLATE utf8_unicode_ci,
   `level` int(11) NOT NULL DEFAULT '0',
   `ancestors_cache` longtext COLLATE utf8_unicode_ci,
   `sons_cache` longtext COLLATE utf8_unicode_ci,
   `is_helpdesk_visible` int(11) NOT NULL default '1',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   UNIQUE KEY `unicity` (`entities_id`,`plugin_resources_choiceitems_id`,`name`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `plugin_resources_choiceitems_id` (`plugin_resources_choiceitems_id`),
   KEY `is_recursive` (`is_recursive`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_resources_items`;
CREATE TABLE `glpi_plugin_resources_resources_items` (
   `id` int(11) NOT NULL auto_increment,
   `plugin_resources_resources_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_resources (id)',
   `items_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various table, according to itemtype (id)',
   `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   UNIQUE KEY `unicity` (`plugin_resources_resources_id`,`itemtype`,`items_id`),
   KEY `FK_device` (`items_id`,`itemtype`),
   KEY `item` (`itemtype`,`items_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_employees`;
CREATE TABLE `glpi_plugin_resources_employees` (
   `id` int(11) NOT NULL auto_increment,
   `plugin_resources_resources_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_resources (id)',
   `plugin_resources_employers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_employers (id)',
   `plugin_resources_clients_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_clients (id)',
   PRIMARY KEY  (`id`),
   KEY `plugin_resources_resources_id` (`plugin_resources_resources_id`),
   KEY `plugin_resources_employers_id` (`plugin_resources_employers_id`),
   KEY `plugin_resources_clients_id` (`plugin_resources_clients_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_employers`;
CREATE TABLE `glpi_plugin_resources_employers` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `short_name` varchar(255) collate utf8_unicode_ci default NULL,
   `locations_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_locations (id)',
   `plugin_resources_employers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_employers (id)',
   `code` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   `completename` text collate utf8_unicode_ci default NULL,
   `level` int(11) NOT NULL DEFAULT '0',
   `ancestors_cache` longtext collate utf8_unicode_ci default NULL,
   `sons_cache` longtext collate utf8_unicode_ci default NULL,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `locations_id` (`locations_id`),
   KEY `plugin_resources_employers_id` (`plugin_resources_employers_id`),
   KEY `is_recursive` (`is_recursive`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_clients`;
CREATE TABLE `glpi_plugin_resources_clients` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `security_compliance` tinyint(1) NOT NULL DEFAULT '0',
   `comment` text collate utf8_unicode_ci,
   `security_and` tinyint(1) NOT NULL default '0',
   `security_fifour` tinyint(1) NOT NULL default '0',
   `security_gisf` tinyint(1) NOT NULL default '0',
   `security_cfi` tinyint(1) NOT NULL default '0',
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `is_recursive` (`is_recursive`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_contracttypes`;
CREATE TABLE `glpi_plugin_resources_contracttypes` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `code` varchar(255) collate utf8_unicode_ci default NULL,
   `use_employee_wizard` tinyint(1) NOT NULL DEFAULT '1',
   `use_need_wizard` tinyint(1) NOT NULL DEFAULT '1',
   `use_picture_wizard` tinyint(1) NOT NULL DEFAULT '1',
   `use_habilitation_wizard` tinyint(1) NOT NULL DEFAULT '0',
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `is_recursive` (`is_recursive`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_departments`;
CREATE TABLE `glpi_plugin_resources_departments` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `is_recursive` (`is_recursive`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_tasks`;
CREATE TABLE `glpi_plugin_resources_tasks` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   `plugin_resources_resources_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_resources (id)',
   `plugin_resources_tasktypes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_tasktypes (id)',
   `users_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
   `groups_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_groups (id)',
   `actiontime` int(11) NOT NULL DEFAULT '0',
   `is_finished` tinyint(1) NOT NULL default '0',
   `comment` text collate utf8_unicode_ci,
   `is_deleted` tinyint(1) NOT NULL default '0',
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `plugin_resources_resources_id` (`plugin_resources_resources_id`),
   KEY `plugin_resources_tasktypes_id` (`plugin_resources_tasktypes_id`),
   KEY `users_id` (`users_id`),
   KEY `groups_id` (`groups_id`),
   KEY `is_finished` (`is_finished`),
   KEY `is_deleted` (`is_deleted`),
   KEY `is_recursive` (`is_recursive`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_taskplannings`;
CREATE TABLE `glpi_plugin_resources_taskplannings` (
   `id` int(11) NOT NULL auto_increment,
   `plugin_resources_tasks_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_tasks (id)',
   `begin` datetime default NULL,
   `end` datetime default NULL,
   PRIMARY KEY  (`id`),
   KEY `begin` (`begin`),
   KEY `end` (`end`),
   KEY `plugin_resources_tasks_id` (`plugin_resources_tasks_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_tasktypes`;
CREATE TABLE `glpi_plugin_resources_tasktypes` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `is_recursive` (`is_recursive`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_tasks_items`;
CREATE TABLE `glpi_plugin_resources_tasks_items` (
   `id` int(11) NOT NULL auto_increment,
   `plugin_resources_tasks_id` int(11) NOT NULL default '0',
   `items_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various table, according to itemtype (id)',
   `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
   PRIMARY KEY  (`id`),
   UNIQUE KEY `unicity` (`plugin_resources_tasks_id`,`itemtype`,`items_id`),
   KEY `FK_device` (`items_id`,`itemtype`),
   KEY `item` (`itemtype`,`items_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_checklists`;
CREATE TABLE `glpi_plugin_resources_checklists` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `entities_id` int(11) NOT NULL default '0',
   `plugin_resources_resources_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_resources (id)',
   `plugin_resources_tasks_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_tasks (id)',
   `plugin_resources_contracttypes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_contracttypes (id)',
   `checklist_type` int(11) NOT NULL default '0',
   `tag` tinyint(1) NOT NULL default '0',
   `is_checked` tinyint(1) NOT NULL default '0',
   `address` varchar(255) collate utf8_unicode_ci default NULL,
   `rank` smallint(6) NOT NULL default '0',
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `plugin_resources_resources_id` (`plugin_resources_resources_id`),
   KEY `plugin_resources_tasks_id` (`plugin_resources_tasks_id`),
   KEY `plugin_resources_contracttypes_id` (`plugin_resources_contracttypes_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_checklistconfigs`;
CREATE TABLE `glpi_plugin_resources_checklistconfigs` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `entities_id` int(11) NOT NULL default '0',
   `tag` tinyint(1) NOT NULL default '0',
   `address` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   `is_deleted` tinyint(1) NOT NULL default '0',
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `is_deleted` (`is_deleted`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_ticketcategories`;
CREATE TABLE `glpi_plugin_resources_ticketcategories` (
   `id` int(11) NOT NULL auto_increment,
   `ticketcategories_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_ticketcategories (id)',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_profiles`;
CREATE TABLE `glpi_plugin_resources_profiles` (
   `id` int(11) NOT NULL auto_increment,
   `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
   `resources` char(1) collate utf8_unicode_ci default NULL,
   `task` char(1) collate utf8_unicode_ci default NULL,
   `checklist` char(1) collate utf8_unicode_ci default NULL,
   `all` char(1) collate utf8_unicode_ci default NULL,
   `employee` char(1) collate utf8_unicode_ci default NULL,
   `resting` char(1) collate utf8_unicode_ci default NULL,
   `holiday` char(1) collate utf8_unicode_ci default NULL,
   `open_ticket` char(1) collate utf8_unicode_ci default NULL,
   `employment` char(1) collate utf8_unicode_ci default NULL,
   `budget` char(1) collate utf8_unicode_ci default NULL,
   `dropdown_public` char(1) collate utf8_unicode_ci default NULL,
   PRIMARY KEY  (`id`),
   KEY `profiles_id` (`profiles_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_reportconfigs`;
CREATE TABLE `glpi_plugin_resources_reportconfigs` (
   `id` int(11) NOT NULL auto_increment,
   `plugin_resources_resources_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_resources (id)',
        `send_report_notif` tinyint(1) NOT NULL default '1',
        `send_other_notif` tinyint(1) NOT NULL default '0',
        `send_transfer_notif` tinyint(1) NOT NULL default '0',
   `comment` text collate utf8_unicode_ci,
   `information` text collate utf8_unicode_ci,
  PRIMARY KEY  (`id`),
  KEY `plugin_resources_resources_id` (`plugin_resources_resources_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_resourcerestings`;
CREATE TABLE `glpi_plugin_resources_resourcerestings` (
   `id` int(11) NOT NULL auto_increment,
   `plugin_resources_resources_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_resources (id)',
   `date_begin` date default NULL,
   `date_end` date default NULL,
   `locations_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_locations (id)',
   `at_home` tinyint(1) NOT NULL default '0',
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `plugin_resources_resources_id` (`plugin_resources_resources_id`),
   KEY `locations_id` (`locations_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `glpi_plugin_resources_resourceholidays` (
   `id` int(11) NOT NULL auto_increment,
   `plugin_resources_resources_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_resources (id)',
   `date_begin` date default NULL,
   `date_end` date default NULL,
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `plugin_resources_resources_id` (`plugin_resources_resources_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_resourcesituations`;
CREATE TABLE `glpi_plugin_resources_resourcesituations` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `code` varchar(255) collate utf8_unicode_ci default NULL,
   `short_name` varchar(255) collate utf8_unicode_ci default NULL,
   `is_contract_linked` tinyint(1) NOT NULL DEFAULT '0',
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `is_recursive` (`is_recursive`),
   KEY `is_contract_linked` (`is_contract_linked`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_contractnatures`;
CREATE TABLE `glpi_plugin_resources_contractnatures` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `code` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `is_recursive` (`is_recursive`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_ranks`;
CREATE TABLE `glpi_plugin_resources_ranks` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `code` varchar(255) collate utf8_unicode_ci default NULL,
   `short_name` varchar(255) collate utf8_unicode_ci default NULL,
   `plugin_resources_professions_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_professions (id)',
   `is_active` tinyint(1) NOT NULL default '0',
   `comment` text collate utf8_unicode_ci,
   `begin_date` date default NULL,
   `end_date` date default NULL,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `is_recursive` (`is_recursive`),
   KEY `plugin_resources_professions_id` (`plugin_resources_professions_id`),
   KEY `is_active` (`is_active`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_resourcespecialities`;
CREATE TABLE `glpi_plugin_resources_resourcespecialities` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `plugin_resources_ranks_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_ranks (id)',
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `is_recursive` (`is_recursive`),
   KEY `plugin_resources_ranks_id` (`plugin_resources_ranks_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_leavingreasons`;
CREATE TABLE `glpi_plugin_resources_leavingreasons` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `is_recursive` (`is_recursive`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_professions`;
CREATE TABLE `glpi_plugin_resources_professions` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `code` varchar(255) collate utf8_unicode_ci default NULL,
   `short_name` varchar(255) collate utf8_unicode_ci default NULL,
   `plugin_resources_professionlines_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_professionlines (id)',
   `plugin_resources_professioncategories_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_professioncategories (id)',
   `is_active` tinyint(1) NOT NULL default '0',
   `comment` text collate utf8_unicode_ci,
   `begin_date` date default NULL,
   `end_date` date default NULL,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `is_recursive` (`is_recursive`),
   KEY `plugin_resources_professionlines_id` (`plugin_resources_professionlines_id`),
   KEY `plugin_resources_professioncategories_id` (`plugin_resources_professioncategories_id`),
   KEY `is_active` (`is_active`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_professionlines`;
CREATE TABLE `glpi_plugin_resources_professionlines` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `code` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `is_recursive` (`is_recursive`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_professioncategories`;
CREATE TABLE `glpi_plugin_resources_professioncategories` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `code` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `is_recursive` (`is_recursive`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_employments`;
CREATE TABLE `glpi_plugin_resources_employments` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   `plugin_resources_resources_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_resources (id)',
   `plugin_resources_professions_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_professions (id)',
   `plugin_resources_ranks_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_resources_ranks (id)',
   `plugin_resources_employmentstates_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_employmentstates (id)',
   `plugin_resources_employers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_employers (id)',
   `ratio_employment_budget` decimal(10,2) NOT NULL default '0',
   `begin_date` date default NULL,
   `end_date` date default NULL,
   `date_mod` datetime default NULL,
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `plugin_resources_resources_id` (`plugin_resources_resources_id`),
   KEY `plugin_resources_professions_id` (`plugin_resources_professions_id`),
   KEY `plugin_resources_ranks_id` (`plugin_resources_ranks_id`),
   KEY `plugin_resources_employmentstates_id` (`plugin_resources_employmentstates_id`),
   KEY `plugin_resources_employers_id` (`plugin_resources_employers_id`),
   KEY `entities_id` (`entities_id`),
   KEY `date_mod` (`date_mod`),
   KEY `is_recursive` (`is_recursive`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_employmentstates`;
CREATE TABLE `glpi_plugin_resources_employmentstates` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `short_name` varchar(255) collate utf8_unicode_ci default NULL,
   `is_active` tinyint(1) NOT NULL default '0',
   `is_leaving_state` tinyint(1) NOT NULL default '0',
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `is_recursive` (`is_recursive`),
   KEY `is_active` (`is_active`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_budgets`;
CREATE TABLE `glpi_plugin_resources_budgets` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `plugin_resources_professions_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_professions (id)',
   `plugin_resources_ranks_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_ranks (id)',
   `plugin_resources_budgettypes_id` int(11) NOT NULL default '0'COMMENT 'RELATION to glpi_plugin_resources_budgettypes (id)',
   `plugin_resources_budgetvolumes_id` int(11) NOT NULL default '0'COMMENT 'RELATION to glpi_plugin_resources_budgetvolumes (id)',
   `begin_date` date default NULL,
   `end_date` date default NULL,
   `volume` int(11) NOT NULL default '0',
   `date_mod` datetime default NULL,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `plugin_resources_professions_id` (`plugin_resources_professions_id`),
   KEY `plugin_resources_ranks_id` (`plugin_resources_ranks_id`),
   KEY `plugin_resources_budgettypes_id` (`plugin_resources_budgettypes_id`),
   KEY `plugin_resources_budgetvolumes_id` (`plugin_resources_budgetvolumes_id`),
   KEY `date_mod` (`date_mod`),
   KEY `entities_id` (`entities_id`),
   KEY `is_recursive` (`is_recursive`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_costs`;
CREATE TABLE `glpi_plugin_resources_costs` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `plugin_resources_professions_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_professions (id)',
   `plugin_resources_ranks_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_ranks (id)',
   `begin_date` date default NULL,
   `end_date` date default NULL,
   `cost` decimal(10,2) NOT NULL default '0',
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `plugin_resources_professions_id` (`plugin_resources_professions_id`),
   KEY `plugin_resources_ranks_id` (`plugin_resources_ranks_id`),
   KEY `entities_id` (`entities_id`),
   KEY `is_recursive` (`is_recursive`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_budgettypes`;
CREATE TABLE `glpi_plugin_resources_budgettypes` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `code` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `is_recursive` (`is_recursive`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
--
DROP TABLE IF EXISTS `glpi_plugin_resources_budgetvolumes`;
CREATE TABLE `glpi_plugin_resources_budgetvolumes` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `code` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `is_recursive` (`is_recursive`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



DROP TABLE IF EXISTS `glpi_plugin_resources_resources_changes`;
CREATE TABLE `glpi_plugin_resources_resources_changes` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `actions_id` tinyint(1) NOT NULL DEFAULT '0',
   `itilcategories_id` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `entities_id` (`entities_id`),
   KEY `itilcategories_id` (`itilcategories_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `glpi_plugin_resources_notifications`;
CREATE TABLE `glpi_plugin_resources_notifications` (
  `id` int(11) NOT NULL auto_increment,
  `plugin_resources_resources_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_resources_resources (id)',
  `date_mod` datetime default NULL,
  `users_id` int(11) NOT NULL default '0',
  `type` varchar(50) default NULL,
  PRIMARY KEY  (`id`),
  KEY `users_id` (`users_id`),
  KEY `date_mod` (`date_mod`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_transferentities`;
CREATE TABLE `glpi_plugin_resources_transferentities` (
  `id` int(11) NOT NULL auto_increment,
  `entities_id` int(11) NOT NULL default '0',
  `groups_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `entities_id` (`entities_id`),
  KEY `groups_id` (`groups_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_resourcebadges`;
CREATE TABLE `glpi_plugin_resources_resourcebadges` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `plugin_metademands_metademands_id` tinyint(1) NOT NULL DEFAULT '0',
   PRIMARY KEY  (`id`),
   KEY `entities_id` (`entities_id`),
   KEY `plugin_metademands_metademands_id` (`plugin_metademands_metademands_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_confighabilitations`;
CREATE TABLE `glpi_plugin_resources_confighabilitations` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `action` tinyint(1) NOT NULL DEFAULT '0',
   `plugin_metademands_metademands_id` tinyint(1) NOT NULL DEFAULT '0',
   PRIMARY KEY  (`id`),
   KEY `entities_id` (`entities_id`),
   KEY `plugin_metademands_metademands_id` (`plugin_metademands_metademands_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_habilitationlevels`;
CREATE TABLE `glpi_plugin_resources_habilitationlevels` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `entities_id` int(11) NOT NULL DEFAULT '0',
   `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
   `comment` text COLLATE utf8_unicode_ci,
   `number` tinyint(1) NOT NULL DEFAULT '0',
   `is_mandatory_creating_resource` tinyint(1) NOT NULL DEFAULT '0',
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `is_recursive` (`is_recursive`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_habilitations`;
CREATE TABLE `glpi_plugin_resources_habilitations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) NOT NULL DEFAULT '0',
  `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `plugin_resources_habilitations_id` int(11) NOT NULL default '0',
  `plugin_resources_habilitationlevels_id` int(11) NOT NULL default '0',
  `completename` text COLLATE utf8_unicode_ci,
  `comment` text COLLATE utf8_unicode_ci,
  `level` int(11) NOT NULL DEFAULT '0',
  `ancestors_cache` longtext COLLATE utf8_unicode_ci,
  `sons_cache` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`),
  KEY `plugin_resources_habilitations_id` (`plugin_resources_habilitations_id`),
  KEY `plugin_resources_habilitationlevels_id` (`plugin_resources_habilitationlevels_id`),
  KEY `entities_id` (`entities_id`),
  KEY `is_recursive` (`is_recursive`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_resourcehabilitations`;
CREATE TABLE `glpi_plugin_resources_resourcehabilitations` (
  `id` int(11) NOT NULL auto_increment,
  `plugin_resources_resources_id` int(11) NOT NULL default '0',
  `plugin_resources_habilitations_id` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY `plugin_resources_resources_id` (`plugin_resources_resources_id`),
  KEY `glpi_plugin_resources_habilitations_id` (`plugin_resources_habilitations_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_configs`;
CREATE TABLE `glpi_plugin_resources_configs` (
   `id` int(11) NOT NULL auto_increment,
   `security_display` tinyint(1) NOT NULL default '0',
   `security_compliance` tinyint(1) NOT NULL default '0',
   `import_external_datas` tinyint(1) NOT NULL DEFAULT '0',
   `resource_manager` varchar(255) NOT NULL DEFAULT '',
   `sales_manager` varchar(255) NOT NULL DEFAULT '',
   PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_resources_configs` VALUES(1, 0, 0, 0,'','');

DROP TABLE IF EXISTS `glpi_plugin_resources_imports`;
CREATE TABLE `glpi_plugin_resources_imports` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
   `comment` text COLLATE utf8_unicode_ci,
   `is_active` tinyint(1) NOT NULL default '0',
   `is_deleted` tinyint(1) NOT NULL default '0',
   `date_creation` datetime DEFAULT NULL,
   `date_mod` datetime DEFAULT NULL,
   PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_importcolumns`;
CREATE TABLE `glpi_plugin_resources_importcolumns` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
   `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
   `resource_column` int(11) NOT NULL,
   `is_identifier` tinyint(1) NOT NULL default '0',
   `plugin_resources_imports_id` int(11) NOT NULL DEFAULT '0',
   PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_importresources`;
CREATE TABLE `glpi_plugin_resources_importresources` (
   `id` int(11) NOT NULL auto_increment,
   `date_creation` datetime DEFAULT NULL,
   `plugin_resources_imports_id` int(11) NOT NULL DEFAULT '0',
   PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_importresourcedatas`;
CREATE TABLE `glpi_plugin_resources_importresourcedatas`(
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NULL,
  `plugin_resources_importresources_id` int(11) NOT NULL DEFAULT '0',
  `plugin_resources_importcolumns_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_resources_resourceimports`;
CREATE TABLE `glpi_plugin_resources_resourceimports` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
   `value` varchar(255) COLLATE utf8_unicode_ci NULL,
   `plugin_resources_resources_id` int(11) NOT NULL DEFAULT '0',
   PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResource','2','1','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResource','3','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResource','4','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResource','5','4','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResource','6','5','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResource','8','6','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesTask','2','1','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesTask','3','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesTask','4','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesTask','6','4','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesTask','7','5','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','34','1','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','9','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','4320','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','3','4','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','5','5','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','10','6','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','6','7','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','11','8','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','4313','9','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','4314','10','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesDirectory','4316','11','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesChecklistconfig','2','1','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesChecklistconfig','3','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesChecklistconfig','4','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResourceResting','3','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResourceResting','4','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResourceResting','5','4','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResourceResting','6','5','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResourceHoliday','2','1','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResourceHoliday','3','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResourceHoliday','4','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesResourceHoliday','5','4','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesEmployment','9','1','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesEmployment','5','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesEmployment','4','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesEmployment','6','4','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesEmployment','7','5','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesEmployment','8','6','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesEmployment','10','7','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesBudget','6','1','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesBudget','7','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesBudget','4','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesBudget','3','4','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesBudget','5','5','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesBudget','8','6','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesBudget','9','7','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4350','1','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4351','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4352','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4353','4','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4354','5','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4355','6','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4356','7','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4357','8','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4358','9','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4359','10','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4360','11','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4361','12','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4362','13','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4363','14','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4364','15','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4365','16','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4366','17','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4367','18','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4368','19','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4369','20','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginResourcesRecap','4370','21','0');

INSERT INTO `glpi_notificationtemplates` (name, itemtype)
VALUES('Resources', 'PluginResourcesResource');
INSERT INTO `glpi_notificationtemplates` (name, itemtype)
VALUES('Alert Resources Tasks', 'PluginResourcesResource');
INSERT INTO `glpi_notificationtemplates` (name, itemtype)
VALUES('Alert Leaving Resources', 'PluginResourcesResource');
INSERT INTO `glpi_notificationtemplates` (name, itemtype)
VALUES('Alert Resources Checklists', 'PluginResourcesResource');
INSERT INTO `glpi_notificationtemplates` (name, itemtype)
VALUES('Leaving Resource', 'PluginResourcesResource');
INSERT INTO `glpi_notificationtemplates` (name, itemtype)
VALUES('Resource Report Creation', 'PluginResourcesResource');
INSERT INTO `glpi_notificationtemplates` (name, itemtype)
VALUES('Resource Resting', 'PluginResourcesResource');
INSERT INTO `glpi_notificationtemplates` (name, itemtype)
VALUES('Resource Holiday', 'PluginResourcesResource');
INSERT INTO `glpi_notificationtemplates` (name, itemtype)
VALUES('Resource Holiday', 'PluginResourcesResource');
INSERT INTO `glpi_notificationtemplates` (name, itemtype)
VALUES('Resources list of commercial manager', 'PluginResourcesResource');
INSERT INTO `glpi_notificationtemplates` (name, itemtype)
VALUES('Send other resource notification', 'PluginResourcesResource');
INSERT INTO `glpi_notificationtemplates` (name, itemtype)
VALUES('Resource Transfer', 'PluginResourcesResource');
INSERT INTO `glpi_notificationtemplates` (name, itemtype)
VALUES('Alert for sales people', 'PluginResourcesResource');