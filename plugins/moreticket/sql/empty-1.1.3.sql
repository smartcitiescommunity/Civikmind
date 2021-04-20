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
  `id`                                INT(11) NOT NULL        AUTO_INCREMENT,
  `name`                              VARCHAR(255)
                                      COLLATE utf8_unicode_ci DEFAULT NULL, -- nom du type d'attente
  `comment`                           TEXT COLLATE utf8_unicode_ci,
  `plugin_moreticket_waitingtypes_id` INT(11) NOT NULL        DEFAULT '0',
  `completename`                      TEXT COLLATE utf8_unicode_ci,
  `level`                             INT(11) NOT NULL        DEFAULT '0',
  `ancestors_cache`                   LONGTEXT COLLATE utf8_unicode_ci,
  `sons_cache`                        LONGTEXT COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `unicity` (`plugin_moreticket_waitingtypes_id`, `name`)
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
  `id`                 INT(11)    NOT NULL AUTO_INCREMENT,
  `use_waiting`        TINYINT(1) NOT NULL DEFAULT '0',
  `use_solution`       TINYINT(1) NOT NULL DEFAULT '0',
  `close_informations` TINYINT(1) NOT NULL DEFAULT '0',
  `solution_status`    TEXT COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

INSERT INTO `glpi_plugin_moreticket_configs` (`id`, `use_waiting`, `use_solution`, `close_informations`, `solution_status`)
VALUES (1, 1, 1, 0, '{"5":1}');

-- --------------------------------------------------------
-- 
-- Structure de la table 'glpi_plugin_moreticket_closetickets'
-- informations pour un ticket 'clos'
-- 
DROP TABLE IF EXISTS `glpi_plugin_moreticket_closetickets`;
CREATE TABLE `glpi_plugin_moreticket_closetickets` (
  `id`            INT(11) NOT NULL        AUTO_INCREMENT,
  `tickets_id`    INT(11) NOT NULL, -- id du ticket GLPI
  `date`          VARCHAR(255)
                  COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment`       TEXT COLLATE utf8_unicode_ci,
  `requesters_id` INT(11) NOT NULL        DEFAULT '0',
  `documents_id`  INT(11) NOT NULL        DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY (`tickets_id`),
  KEY (`documents_id`),
  KEY (`requesters_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;
