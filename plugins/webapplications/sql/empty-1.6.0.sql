DROP TABLE IF EXISTS `glpi_plugin_webapplications_webapplications`;
CREATE TABLE `glpi_plugin_webapplications_webapplications` (
  `id`                                                  INT(11)    NOT NULL     AUTO_INCREMENT,
  `entities_id`                                         INT(11)    NOT NULL     DEFAULT '0',
  `is_recursive`                                        TINYINT(1) NOT NULL     DEFAULT '0',
  `name`                                                VARCHAR(255)
                                                        COLLATE utf8_unicode_ci DEFAULT NULL,
  `address`                                             VARCHAR(255)
                                                        COLLATE utf8_unicode_ci DEFAULT NULL,
  `backoffice`                                          VARCHAR(255)
                                                        COLLATE utf8_unicode_ci DEFAULT NULL,
  `plugin_webapplications_webapplicationtypes_id`       INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_plugin_webapplications_webapplicationtypes (id)',
  `plugin_webapplications_webapplicationservertypes_id` INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_plugin_webapplications_webapplicationservertypes (id)',
  `plugin_webapplications_webapplicationtechnics_id`    INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_plugin_webapplications_webapplicationtechnics (id)',
  `version`                                             VARCHAR(255)
                                                        COLLATE utf8_unicode_ci DEFAULT NULL,
  `users_id`                                            INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_users (id)',
  `groups_id`                                           INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_groups (id)',
  `suppliers_id`                                        INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_suppliers (id)',
  `manufacturers_id`                                    INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_manufacturers (id)',
  `locations_id`                                        INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_locations (id)',
  `date_mod`                                            DATETIME                DEFAULT NULL,
  `is_helpdesk_visible`                                 INT(11)    NOT NULL     DEFAULT '1',
  `notepad`                                             LONGTEXT COLLATE utf8_unicode_ci,
  `comment`                                             TEXT COLLATE utf8_unicode_ci,
  `is_deleted`                                          TINYINT(1) NOT NULL     DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `entities_id` (`entities_id`),
  KEY `plugin_webapplications_webapplicationtypes_id` (`plugin_webapplications_webapplicationtypes_id`),
  KEY `plugin_webapplications_webapplicationservertypes_id` (`plugin_webapplications_webapplicationservertypes_id`),
  KEY `plugin_webapplications_webapplicationtechnics_id` (`plugin_webapplications_webapplicationtechnics_id`),
  KEY `users_id` (`users_id`),
  KEY `groups_id` (`groups_id`),
  KEY `suppliers_id` (`suppliers_id`),
  KEY `manufacturers_id` (`manufacturers_id`),
  KEY `locations_id` (`locations_id`),
  KEY date_mod (date_mod),
  KEY is_helpdesk_visible (is_helpdesk_visible),
  KEY `is_deleted` (`is_deleted`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_webapplications_webapplicationtypes`;
CREATE TABLE `glpi_plugin_webapplications_webapplicationtypes` (
  `id`          INT(11) NOT NULL        AUTO_INCREMENT,
  `entities_id` INT(11) NOT NULL        DEFAULT '0',
  `name`        VARCHAR(255)
                COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment`     TEXT COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `entities_id` (`entities_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_webapplications_webapplicationservertypes`;
CREATE TABLE `glpi_plugin_webapplications_webapplicationservertypes` (
  `id`      INT(11) NOT NULL        AUTO_INCREMENT,
  `name`    VARCHAR(255)
            COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` TEXT COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

INSERT INTO `glpi_plugin_webapplications_webapplicationservertypes` VALUES ('1', 'Apache', '');
INSERT INTO `glpi_plugin_webapplications_webapplicationservertypes` VALUES ('2', 'IIS', '');
INSERT INTO `glpi_plugin_webapplications_webapplicationservertypes` VALUES ('3', 'Tomcat', '');

DROP TABLE IF EXISTS `glpi_plugin_webapplications_webapplicationtechnics`;
CREATE TABLE `glpi_plugin_webapplications_webapplicationtechnics` (
  `id`      INT(11) NOT NULL        AUTO_INCREMENT,
  `name`    VARCHAR(255)
            COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` TEXT COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

INSERT INTO `glpi_plugin_webapplications_webapplicationtechnics` VALUES ('1', 'Asp', '');
INSERT INTO `glpi_plugin_webapplications_webapplicationtechnics` VALUES ('2', 'Cgi', '');
INSERT INTO `glpi_plugin_webapplications_webapplicationtechnics` VALUES ('3', 'Java', '');
INSERT INTO `glpi_plugin_webapplications_webapplicationtechnics` VALUES ('4', 'Perl', '');
INSERT INTO `glpi_plugin_webapplications_webapplicationtechnics` VALUES ('5', 'Php', '');
INSERT INTO `glpi_plugin_webapplications_webapplicationtechnics` VALUES ('6', '.Net', '');

DROP TABLE IF EXISTS `glpi_plugin_webapplications_webapplications_items`;
CREATE TABLE `glpi_plugin_webapplications_webapplications_items` (
  `id`                                        INT(11)                 NOT NULL AUTO_INCREMENT,
  `plugin_webapplications_webapplications_id` INT(11)                 NOT NULL DEFAULT '0'
  COMMENT 'RELATION to glpi_plugin_webapplications_webapplications (id)',
  `items_id`                                  INT(11)                 NOT NULL DEFAULT '0'
  COMMENT 'RELATION to various tables, according to itemtype (id)',
  `itemtype`                                  VARCHAR(100)
                                              COLLATE utf8_unicode_ci NOT NULL
  COMMENT 'see .class.php file',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unicity` (`plugin_webapplications_webapplications_id`, `items_id`, `itemtype`),
  KEY `FK_device` (`items_id`, `itemtype`),
  KEY `item` (`itemtype`, `items_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_webapplications_profiles`;
CREATE TABLE `glpi_plugin_webapplications_profiles` (
  `id`              INT(11) NOT NULL        AUTO_INCREMENT,
  `profiles_id`     INT(11) NOT NULL        DEFAULT '0'
  COMMENT 'RELATION to glpi_profiles (id)',
  `webapplications` CHAR(1)
                    COLLATE utf8_unicode_ci DEFAULT NULL,
  `open_ticket`     CHAR(1)
                    COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `profiles_id` (`profiles_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

INSERT INTO `glpi_displaypreferences` VALUES (NULL, 'PluginWebapplicationsWebapplication', '2', '2', '0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL, 'PluginWebapplicationsWebapplication', '3', '4', '0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL, 'PluginWebapplicationsWebapplication', '6', '5', '0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL, 'PluginWebapplicationsWebapplication', '7', '6', '0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL, 'PluginWebapplicationsWebapplication', '8', '7', '0');