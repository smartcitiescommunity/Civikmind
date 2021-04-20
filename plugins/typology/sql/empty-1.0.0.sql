--
-- Plugin Typology
--

-- -------------------------------------------------------------------------------------------------
--
-- Structure de la table 'glpi_plugin_typology_profiles'
-- gestion des droits pour le plugin
--

DROP TABLE IF EXISTS `glpi_plugin_typology_profiles`;
CREATE TABLE `glpi_plugin_typology_profiles` (
  `id` int(11) NOT NULL auto_increment, -- id du profil
  `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)', -- lien avec profiles de glpi
  `typology` char(1) collate utf8_unicode_ci default NULL, -- $right:  r (can view), w (can update)
  PRIMARY KEY  (`id`),
  KEY `profiles_id` (`profiles_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
--
-- Structure de la table 'glpi_plugin_typology_typologies'
-- Liste des profils d'usage � cr�er lors de la configuration
--

DROP TABLE IF EXISTS `glpi_plugin_typology_typologies`;
CREATE TABLE `glpi_plugin_typology_typologies` (
  `id` int(11) NOT NULL AUTO_INCREMENT, -- id du profil d'usage
  `entities_id` int(11) NOT NULL default '0', -- � laisser pour l'utilisation des entit�s
  `is_recursive` tinyint(1) NOT NULL default '0', -- � laisser pour l'utilisation de la r�cursivit�
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL, -- nom du profil ex: Poste M�decin, ...
  `comment` text COLLATE utf8_unicode_ci, -- commentaires si n�cessaire
  `notepad` longtext collate utf8_unicode_ci,
  `is_deleted` tinyint(1) NOT NULL default '0', -- pour placer les profils dans la corbeille avant purge d�finitive ou restoration
  `date_mod` datetime NULL default NULL, -- date de derni�re modif du profil (quand il y a modification de la liste de mat�riels li�s � ce type de profil d'usage)
  PRIMARY KEY (`id`), -- index
  KEY `name` (`name`), -- index
  KEY `entities_id` (`entities_id`), -- index
  KEY `is_recursive` (`is_recursive`), -- index
  KEY `is_deleted` (`is_deleted`) -- index
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- --------------------------------------------------------
-- 
-- Structure de la table 'glpi_plugin_typology_typologycriterias'
-- Liste des crit�res pour chaque profils (mat�riels li�s) (cf glpi_slalevels)
-- 

DROP TABLE IF EXISTS `glpi_plugin_typology_typologycriterias`;
CREATE TABLE `glpi_plugin_typology_typologycriterias` (
  `id` int(11) NOT NULL auto_increment, -- id ...
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL, -- nom du crit�re, ex: type d'�cran
  `plugin_typology_typologies_id` int(11) NOT NULL default '0', -- lien avec le profil d'usage
  `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',-- lien avec la classe de l'objet (ex : ordi, moniteur, p�riph�rique ...)
  `link` tinyint(1) NOT NULL default '0',-- AND ou OR entre les définitions d'un critère
  `entities_id` int(11) NOT NULL default '0', -- � laisser pour l'utilisation des entit�s
  `is_recursive` tinyint(1) NOT NULL default '0', -- � laisser pour l'utilisation de la r�cursivit�
  `date_mod` datetime default NULL,
  `is_active` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`), -- index
  KEY `name` (`name`), -- index
  KEY `plugin_typology_typologies_id` (`plugin_typology_typologies_id`) -- index
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- --------------------------------------------------------
-- 
-- Structure de la table 'glpi_plugin_typology_typologycriteriadefinitions'
-- D�finition des crit�res pour chaque profils (champ et valeur) (cf glpi_slalevelactions)
-- 

DROP TABLE IF EXISTS `glpi_plugin_typology_typologycriteriadefinitions`;
CREATE TABLE `glpi_plugin_typology_typologycriteriadefinitions` (
  `id` int(11) NOT NULL auto_increment, -- id ...
  `plugin_typology_typologycriterias_id` int(11) NOT NULL default '0', -- lien avec le crit�re du profil d'usage
  `field` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,-- info suppl sur l'objet taille (�cran), Nom (logiciel) ...
  `action_type` varchar(100) collate utf8_unicode_ci NOT NULL,-- action de type contient, est ...
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,-- valeur attendue pour chaque crit�re (field)
  `entities_id` int(11) NOT NULL default '0', -- � laisser pour l'utilisation des entit�s
  `is_recursive` tinyint(1) NOT NULL default '0', -- � laisser pour l'utilisation de la r�cursivit�
  PRIMARY KEY  (`id`), -- index
  KEY `plugin_typology_typologycriterias_id` (`plugin_typology_typologycriterias_id`) -- index
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- 
-- Structure de la table 'glpi_plugin_typology_items'
-- Affectation de la typologie aux mat�riels 
-- 

DROP TABLE IF EXISTS `glpi_plugin_typology_typologies_items`;
CREATE TABLE `glpi_plugin_typology_typologies_items` (
  `id` int(11) NOT NULL auto_increment, -- id
  `plugin_typology_typologies_id` int(11) NOT NULL default '0', -- lien avec le profil d'usage
  `items_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)',-- nom sp�cifique de l'objet (ex: bloc note 90 pages, ...), nom
  `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',-- lien avec la classe de l'objet (ex : ordi, moniteur, p�riph�rique ...)
  `is_validated` tinyint(1) DEFAULT NULL, -- result from consolemanagement
  `error` longtext collate utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY  (`id`), -- index
  UNIQUE KEY `unicity` (`plugin_typology_typologies_id`,`itemtype`,`items_id`), -- cl� unique pour cet ensemble
  KEY `itemtype` (`itemtype`), -- index
  KEY `items_id` (`items_id`) -- index
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_notificationtemplates` VALUES(NULL, 'Alert no validated typology', 'PluginTypologyTypology', '2012-11-19 15:20:46','',NULL);