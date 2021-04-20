-- ----------------------------------------------------------
-- Plugin Metademands update 2.0.0                        --------------
-- ----------------------------------------------------------

-- ----------------------------------------------------------
--
-- Structure de la table 'glpi_plugin_metademands_itilapplications'
-- 
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_metademands_itilapplications`;
CREATE TABLE `glpi_plugin_metademands_itilapplications` (
  `id` int(11) NOT NULL AUTO_INCREMENT, -- id itilapplications 
  `name` varchar(255) default NULL, -- name itilapplications
  `entities_id` int(11) NOT NULL default '0', -- entites_id
  `is_recursive` int(1) NOT NULL default '0', -- is_recursive
  `comment` text COLLATE utf8_unicode_ci default NULL,
  PRIMARY KEY (`id`),
  KEY `entities_id` (`entities_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_metademands_itilapplications` (`id` ,`name` ,`entities_id` ,`is_recursive`,`comment`) VALUES ('1', 'Aucune', '0', '1', '');

-- ----------------------------------------------------------
--
-- Structure de la table 'glpi_plugin_metademands_itilenvironments'
-- 
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_metademands_itilenvironments`;
CREATE TABLE `glpi_plugin_metademands_itilenvironments` (
  `id` int(11) NOT NULL AUTO_INCREMENT, -- id itilenvironments 
  `name` varchar(255) default NULL, -- name itilenvironments
  `entities_id` int(11) NOT NULL default '0', -- entites_id
  `is_recursive` int(1) NOT NULL default '0', -- is_recursive
  `comment` text COLLATE utf8_unicode_ci default NULL,
  PRIMARY KEY (`id`),
  KEY `entities_id` (`entities_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_metademands_itilenvironments` (`id` ,`name` ,`entities_id` ,`is_recursive`,`comment`) VALUES ('1', 'Sans objet', '0', '1', '');

-- ----------------------------------------------------------
--
-- Structure de la table 'glpi_plugin_metademands_tickets_itilapplications'
-- 
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_metademands_tickets_itilapplications`;
CREATE TABLE `glpi_plugin_metademands_tickets_itilapplications` (
  `id` int(11) NOT NULL AUTO_INCREMENT, -- id itilapplications 
  `tickets_id` varchar(255) default NULL, -- name itilapplications
  `plugin_metademands_itilapplications_id` int(11) NOT NULL default '0',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`plugin_metademands_itilapplications_id`) REFERENCES glpi_plugin_metademands_itilapplications(id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------------------------------------
--
-- Structure de la table 'glpi_plugin_metademands_tickets_itilenvironments'
-- 
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_metademands_tickets_itilenvironments`;
CREATE TABLE `glpi_plugin_metademands_tickets_itilenvironments` (
  `id` int(11) NOT NULL AUTO_INCREMENT, -- id itilenvironments 
  `tickets_id` varchar(255) default NULL, -- name itilenvironments
  `plugin_metademands_itilenvironments_id` int(11) NOT NULL default '0',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`plugin_metademands_itilenvironments_id`) REFERENCES glpi_plugin_metademands_itilenvironments(id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;