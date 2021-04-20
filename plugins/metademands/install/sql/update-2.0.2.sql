-- --------------------------------------------------------
--
-- Structure de la table 'glpi_plugin_metademands_configs'
-- Pemret de configurer les groupes d'affectation pour chaque application
--

DROP TABLE IF EXISTS `glpi_plugin_metademands_configs`;
CREATE TABLE `glpi_plugin_metademands_configs` (
   `id` int(11) NOT NULL auto_increment,
   `simpleticket_to_metademand` tinyint(1) default '0',
   PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_metademands_configs` (`id` ,`simpleticket_to_metademand`) VALUES ('1', '0');