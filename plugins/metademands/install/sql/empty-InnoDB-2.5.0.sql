-- ----------------------------------------------------------
-- Plugin Metademands                          --------------
-- ----------------------------------------------------------

-- -----------------------------------------------------------
--
-- Structure de la table 'glpi_plugin_metademands_metademands'
--
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_metademands_metademands`;
CREATE TABLE `glpi_plugin_metademands_metademands` (
  `id` int(11) NOT NULL AUTO_INCREMENT, -- id metademands
  `name` varchar(255) default NULL, -- name metademands
  `entities_id` int(11) NOT NULL default '0', -- entites_id
  `is_recursive` int(1) NOT NULL default '0', -- is_recursive
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `comment` text COLLATE utf8_unicode_ci default NULL,
  `type` int(11) NOT NULL default '0', -- metademand type : Incident, demand
  `itilcategories_id` int(11) NOT NULL default '0', -- references itilcategories glpi
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- ----------------------------------------------------------
--
-- Structure de la table 'glpi_plugin_metademands_tasks'
--
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_metademands_tasks`;
CREATE TABLE `glpi_plugin_metademands_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) default NULL,
  `completename` varchar(255) default NULL,
  `comment` text COLLATE utf8_unicode_ci default NULL,
  `entities_id` int(11) NOT NULL default '0', -- entites_id
  `level` int(11) NOT NULL default '0',
  `type` int(11) NOT NULL default '0',
  `ancestors_cache` text COLLATE utf8_unicode_ci default NULL,
  `sons_cache` text COLLATE utf8_unicode_ci default NULL,
  `plugin_metademands_tasks_id` int(11) NOT NULL default '0',
  `plugin_metademands_metademands_id` int(11) NOT NULL default '0',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`plugin_metademands_metademands_id`) REFERENCES glpi_plugin_metademands_metademands(id),
  KEY `plugin_metademands_tasks_id` (`plugin_metademands_tasks_id`),
  KEY `entities_id` (`entities_id`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- ------------------------------------------------------------
--
-- Structure de la table 'glpi_plugin_metademands_fields'
--
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_metademands_fields`;
CREATE TABLE `glpi_plugin_metademands_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) NOT NULL default '0', -- entites_id
  `is_recursive` int(1) NOT NULL default '0', -- is_recursive
  `comment` varchar(255) default NULL,
  `custom_values` text COLLATE utf8_unicode_ci default NULL,
  `comment_values` text COLLATE utf8_unicode_ci default NULL,
  `check_value` varchar(255) default NULL,
  `rank` int(1) NOT NULL default '0',
  `order` int(1) NOT NULL default '0',
  `label` varchar(255) default NULL,
  `label2` varchar(255) default NULL,
  `type` varchar(255) default NULL,
  `item` varchar(255) default NULL,
  `is_mandatory` int(1) NOT NULL default '0',
  `plugin_metademands_fields_id` int(11) NOT NULL default '0',
  `plugin_metademands_metademands_id` int(11) NOT NULL default '0',
  `plugin_metademands_tasks_id` int(11) NOT NULL default '0',
  `fields_link` int(11) NOT NULL default '0',
  `color` varchar(255) default NULL,
  `parent_field_id` int(11) NOT NULL default '0',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`plugin_metademands_metademands_id`) REFERENCES glpi_plugin_metademands_metademands(id),
  KEY `plugin_metademands_fields_id` (`plugin_metademands_fields_id`),
  KEY `fields_link` (`fields_link`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- ------------------------------------------------------------
--
-- Structure de la table 'glpi_plugin_metademands_tickets_fields'
--
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_metademands_tickets_fields`;
CREATE TABLE `glpi_plugin_metademands_tickets_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` text COLLATE utf8_unicode_ci default NULL,
  `tickets_id` int(11) NOT NULL default '0',
  `plugin_metademands_fields_id` int(11) NOT NULL default '0',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`plugin_metademands_fields_id`) REFERENCES glpi_plugin_metademands_fields(id),
  FOREIGN KEY (`tickets_id`) REFERENCES glpi_tickets(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ------------------------------------------------------------
--
-- Structure de la table 'glpi_plugin_metademands_ticketfields'
--
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_metademands_ticketfields`;
CREATE TABLE `glpi_plugin_metademands_ticketfields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `num` int(11) default NULL,
  `value` text COLLATE utf8_unicode_ci default NULL,
  `entities_id` int(11) NOT NULL default '0', -- entites_id
  `is_recursive` int(1) NOT NULL default '0', -- is_recursive
  `is_mandatory` int(1) NOT NULL default '0',
  `is_deletable` int(1) NOT NULL default '1',
  `plugin_metademands_metademands_id` int(11) NOT NULL default '0',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`plugin_metademands_metademands_id`) REFERENCES glpi_plugin_metademands_metademands(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ------------------------------------------------------------
--
-- Structure de la table 'glpi_plugin_metademands_tickettasks'
--
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_metademands_tickettasks`;
CREATE TABLE `glpi_plugin_metademands_tickettasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text COLLATE utf8_unicode_ci default NULL,
  `itilcategories_id` int(11) default '0',
  `plugin_metademands_itilapplications_id` int(11) default '0',
  `plugin_metademands_itilenvironments_id` int(11) default '0',
  `type` int(11) NOT NULL default '0',
  `status` varchar(255) default NULL,
  `actiontime` int(11) NOT NULL default '0',
  `requesttypes_id` int(11) NOT NULL default '0',
  `groups_id_assign` int(11) NOT NULL default '0',
  `users_id_assign` int(11) NOT NULL default '0',
  `groups_id_requester` int(11) NOT NULL default '0',
  `users_id_requester` int(11) NOT NULL default '0',
  `groups_id_observer` int(11) NOT NULL default '0',
  `users_id_observer` int(11) NOT NULL default '0',
  `plugin_metademands_tasks_id` int(11) NOT NULL default '0',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`plugin_metademands_tasks_id`) REFERENCES glpi_plugin_metademands_tasks(id),
  KEY `plugin_metademands_itilapplications_id` (`plugin_metademands_itilapplications_id`),
  KEY `plugin_metademands_itilenvironments_id` (`plugin_metademands_itilenvironments_id`),
  KEY `itilcategories_id` (`itilcategories_id`),
  KEY `groups_id_assign` (`groups_id_assign`),
  KEY `users_id_assign` (`users_id_assign`),
  KEY `groups_id_requester` (`groups_id_requester`),
  KEY `users_id_requester` (`users_id_requester`),
  KEY `groups_id_observer` (`groups_id_observer`),
  KEY `users_id_observer` (`users_id_observer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------------------------------------
--
-- Structure de la table 'glpi_plugin_metademands_tickets_tasks'
--
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_metademands_tickets_tasks`;
CREATE TABLE `glpi_plugin_metademands_tickets_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_metademands_tasks_id` int(11) NOT NULL default '0',
  `level` int(11) NOT NULL default '0',
  `tickets_id` int(11) NOT NULL default '0',
  `parent_tickets_id` int(11) NOT NULL default '0',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`plugin_metademands_tasks_id`) REFERENCES glpi_plugin_metademands_tasks(id),
  FOREIGN KEY (`tickets_id`) REFERENCES glpi_tickets(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------------------------------------
--
-- Structure de la table 'glpi_plugin_metademands_tickets_metademands'
--
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_metademands_tickets_metademands`;
CREATE TABLE `glpi_plugin_metademands_tickets_metademands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_metademands_metademands_id` int(11) NOT NULL default '0',
  `tickets_id` int(11) NOT NULL default '0',
  `parent_tickets_id` int(11) NOT NULL default '0',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`plugin_metademands_metademands_id`) REFERENCES glpi_plugin_metademands_metademands(id),
  FOREIGN KEY (`tickets_id`) REFERENCES glpi_tickets(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------------------------------------
--
-- Structure de la table 'glpi_plugin_metademands_metademandtasks'
--
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_metademands_metademandtasks`;
CREATE TABLE `glpi_plugin_metademands_metademandtasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_metademands_metademands_id` int(11) NOT NULL default '0',
  `plugin_metademands_tasks_id` int(11) NOT NULL default '0',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`plugin_metademands_metademands_id`) REFERENCES glpi_plugin_metademands_metademands(id),
  FOREIGN KEY (`plugin_metademands_tasks_id`) REFERENCES glpi_plugin_metademands_tasks(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------------------------------------
--
-- Structure de la table 'glpi_plugin_metademands_groups'
--
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_metademands_groups`;
CREATE TABLE `glpi_plugin_metademands_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groups_id` int(11) NOT NULL default '0',
  `plugin_metademands_metademands_id` int(11) NOT NULL default '0',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`plugin_metademands_metademands_id`) REFERENCES glpi_plugin_metademands_metademands(id),
  FOREIGN KEY (`groups_id`) REFERENCES glpi_groups(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------------------------------------
--
-- Structure de la table 'glpi_plugin_metademands_itilapplications'
--
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_metademands_itilapplications`;
CREATE TABLE `glpi_plugin_metademands_itilapplications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) default NULL,
  `entities_id` int(11) NOT NULL default '0', -- entites_id
  `is_recursive` int(1) NOT NULL default '0', -- is_recursive
  `comment` text COLLATE utf8_unicode_ci default NULL,
  `is_critical` int(1) NOT NULL default '0',
  PRIMARY KEY (`id`),
  KEY `entities_id` (`entities_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_metademands_itilapplications` (`id` ,`name` ,`entities_id` ,`is_recursive`,`comment`, `is_critical`) VALUES ('1', 'Aucune', '0', '1', '', '0');

-- ----------------------------------------------------------
--
-- Structure de la table 'glpi_plugin_metademands_itilenvironments'
--
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_metademands_itilenvironments`;
CREATE TABLE `glpi_plugin_metademands_itilenvironments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) default NULL,
  `entities_id` int(11) NOT NULL default '0', -- entites_id
  `is_recursive` int(1) NOT NULL default '0', -- is_recursive
  `comment` text COLLATE utf8_unicode_ci default NULL,
  `is_outproduction` tinyint(1) NOT NULL default '0',
  PRIMARY KEY (`id`),
  KEY `entities_id` (`entities_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_metademands_itilenvironments` (`id` ,`name` ,`entities_id` ,`is_recursive`,`comment`, `is_outproduction`) VALUES ('1', 'Sans objet', '0', '1', '', '0');

-- ----------------------------------------------------------
--
-- Structure de la table 'glpi_plugin_metademands_tickets_itilapplications'
--
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_metademands_tickets_itilapplications`;
CREATE TABLE `glpi_plugin_metademands_tickets_itilapplications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tickets_id` varchar(255) default NULL,
  `plugin_metademands_itilapplications_id` int(11) NOT NULL default '0',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`plugin_metademands_itilapplications_id`) REFERENCES glpi_plugin_metademands_itilapplications(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------------------------------------
--
-- Structure de la table 'glpi_plugin_metademands_tickets_itilenvironments'
--
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_metademands_tickets_itilenvironments`;
CREATE TABLE `glpi_plugin_metademands_tickets_itilenvironments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tickets_id` varchar(255) default NULL,
  `plugin_metademands_itilenvironments_id` int(11) NOT NULL default '0',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`plugin_metademands_itilenvironments_id`) REFERENCES glpi_plugin_metademands_itilenvironments(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
--
-- Structure de la table 'glpi_plugin_metademands_metademands_resources'
--
-- ------------------------------------------------------------
 DROP TABLE IF EXISTS `glpi_plugin_metademands_metademands_resources`;
      CREATE TABLE `glpi_plugin_metademands_metademands_resources` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
        `entities_id` int(11) NOT NULL default '0',
        `plugin_resources_contracttypes_id` int(11) NOT NULL default '0',
        `plugin_metademands_metademands_id` int(11) NOT NULL default '0',
        PRIMARY KEY (`id`),
        FOREIGN KEY (`plugin_metademands_metademands_id`) REFERENCES glpi_plugin_metademands_metademands(id)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
--
-- Structure de la table 'glpi_plugin_metademands_configs'
--
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_metademands_configs`;
CREATE TABLE `glpi_plugin_metademands_configs` (
   `id` int(11) NOT NULL auto_increment,
   `simpleticket_to_metademand` tinyint(1) default '0',
   `enable_application_environment` tinyint(1) default '0',
   `enable_families` tinyint(1) default '0',
   `parent_ticket_tag` varchar(255) default NULL,
   `son_ticket_tag` varchar(255) default NULL,
   `create_pdf` tinyint(1) default '0',
   `show_requester_informations` tinyint(1) default 0,
   PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_metademands_configs` (`id` ,`simpleticket_to_metademand`) VALUES ('1', '0');