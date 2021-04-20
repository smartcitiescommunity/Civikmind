DROP TABLE IF EXISTS `glpi_plugin_moreticket_profiles`;
CREATE TABLE `glpi_plugin_moreticket_profiles` (
  `id`          INT(11) NOT NULL        AUTO_INCREMENT, -- id du profil
  `profiles_id` INT(11) NOT NULL        DEFAULT '0'
  COMMENT 'RELATION to glpi_profiles (id)', -- lien avec profiles de glpi
  `moreticket`  CHAR(1)
                COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `profiles_id` (`profiles_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

-- --------------------------------------------------------
-- 
-- Structure de la table 'glpi_plugin_moreticket_waitingtickets'
-- Champs supplémentaire à gèrer pour les tickets en attente de GLPI
-- 

DROP TABLE IF EXISTS `glpi_plugin_moreticket_waitingtickets`;
CREATE TABLE `glpi_plugin_moreticket_waitingtickets` (
  `id`                                INT(11) NOT NULL AUTO_INCREMENT, -- id ...
  `tickets_id`                        INT(11) NOT NULL, -- id du ticket GLPI
  `reason`                            VARCHAR(255)     DEFAULT NULL, -- raison de l'attente
  `date_suspension`                   DATETIME         DEFAULT NULL, -- date de suspension
  `date_report`                       DATETIME         DEFAULT NULL, -- date de report
  `date_end_suspension`               DATETIME         DEFAULT NULL, -- date de sortie de suspension
  `plugin_moreticket_waitingtypes_id` INT(11)          DEFAULT NULL, -- id du type d'attente
  PRIMARY KEY (`id`), -- index
  KEY `date_suspension` (`date_suspension`),
  FOREIGN KEY (`tickets_id`) REFERENCES glpi_tickets (id),
  FOREIGN KEY (`plugin_moreticket_waitingtypes_id`) REFERENCES glpi_plugin_moreticket_waitingtypes (id)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

-- --------------------------------------------------------
-- 
-- Structure de la table 'glpi_plugin_moreticket_waitingtypes'
-- Liste des types d'attente pour un ticket 'en attente'
-- 

DROP TABLE IF EXISTS `glpi_plugin_moreticket_waitingtypes`;
CREATE TABLE `glpi_plugin_moreticket_waitingtypes` (
  `id`                                INT(11)    NOT NULL        AUTO_INCREMENT,
  `name`                              VARCHAR(255)
                                      COLLATE utf8_unicode_ci    DEFAULT NULL, -- nom du type d'attente
  `comment`                           TEXT COLLATE utf8_unicode_ci,
  `plugin_moreticket_waitingtypes_id` INT(11)    NOT NULL        DEFAULT '0',
  `completename`                      TEXT COLLATE utf8_unicode_ci,
  `level`                             INT(11)    NOT NULL        DEFAULT '0',
  `ancestors_cache`                   LONGTEXT COLLATE utf8_unicode_ci,
  `sons_cache`                        LONGTEXT COLLATE utf8_unicode_ci,
  `is_helpdeskvisible`                TINYINT(1) NOT NULL        DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `unicity` (`plugin_moreticket_waitingtypes_id`, `name`),
  KEY `is_helpdeskvisible` (`is_helpdeskvisible`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;
-- --------------------------------------------------------
-- 
-- Structure de la table 'glpi_plugin_moreticket_configs'
-- Plugin configuration
-- 

DROP TABLE IF EXISTS `glpi_plugin_moreticket_configs`;
CREATE TABLE `glpi_plugin_moreticket_configs` (
  `id`           INT(11)    NOT NULL AUTO_INCREMENT,
  `use_waiting`  TINYINT(1) NOT NULL DEFAULT '0',
  `use_solution` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

INSERT INTO `glpi_plugin_moreticket_configs` (`id`, `use_waiting`, `use_solution`) VALUES (1, 1, 1);
