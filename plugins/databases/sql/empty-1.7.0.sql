DROP TABLE IF EXISTS `glpi_plugin_databases_databases`;
CREATE TABLE `glpi_plugin_databases_databases` (
  `id`                                     INT(11)    NOT NULL     AUTO_INCREMENT,
  `entities_id`                            INT(11)    NOT NULL     DEFAULT '0',
  `is_recursive`                           TINYINT(1) NOT NULL     DEFAULT '0',
  `name`                                   VARCHAR(255)
                                           COLLATE utf8_unicode_ci DEFAULT NULL,
  `plugin_databases_databasecategories_id` INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_plugin_databases_databasecategories (id)',
  `plugin_databases_databasetypes_id`      INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_plugin_databases_databasetypes (id)',
  `users_id_tech`                          INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_users (id)',
  `groups_id_tech`                         INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_groups (id)',
  `plugin_databases_servertypes_id`        INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_plugin_databases_servertypes (id)',
  `suppliers_id`                           INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_suppliers (id)',
  `manufacturers_id`                       INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_manufacturers (id)',
  `locations_id`                           INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_locations (id)',
  `comment`                                TEXT COLLATE utf8_unicode_ci,
  `is_helpdesk_visible`                    INT(11)    NOT NULL     DEFAULT '1',
  `date_mod`                               DATETIME                DEFAULT NULL,
  `is_deleted`                             TINYINT(1) NOT NULL     DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `entities_id` (`entities_id`),
  KEY `plugin_databases_databasecategories_id` (`plugin_databases_databasecategories_id`),
  KEY `plugin_databases_databasetypes_id` (`plugin_databases_databasetypes_id`),
  KEY `plugin_databases_servertypes_id` (`plugin_databases_servertypes_id`),
  KEY `users_id_tech` (`users_id_tech`),
  KEY `groups_id_tech` (`groups_id_tech`),
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

DROP TABLE IF EXISTS `glpi_plugin_databases_databasetypes`;
CREATE TABLE `glpi_plugin_databases_databasetypes` (
  `id`          INT(11) NOT NULL        AUTO_INCREMENT,
  `entities_id` INT(11) NOT NULL        DEFAULT '0',
  `name`        VARCHAR(255)
                COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment`     TEXT COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_databases_databasecategories`;
CREATE TABLE `glpi_plugin_databases_databasecategories` (
  `id`          INT(11) NOT NULL        AUTO_INCREMENT,
  `entities_id` INT(11) NOT NULL        DEFAULT '0',
  `name`        VARCHAR(255)
                COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment`     TEXT COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_databases_servertypes`;
CREATE TABLE `glpi_plugin_databases_servertypes` (
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

INSERT INTO `glpi_plugin_databases_servertypes` VALUES ('1', 'Mysql', '');
INSERT INTO `glpi_plugin_databases_servertypes` VALUES ('2', 'Oracle', '');
INSERT INTO `glpi_plugin_databases_servertypes` VALUES ('3', 'SQL', '');

DROP TABLE IF EXISTS `glpi_plugin_databases_scripttypes`;
CREATE TABLE `glpi_plugin_databases_scripttypes` (
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

DROP TABLE IF EXISTS `glpi_plugin_databases_instances`;
CREATE TABLE `glpi_plugin_databases_instances` (
  `id`                            INT(11)    NOT NULL     AUTO_INCREMENT,
  `entities_id`                   INT(11)    NOT NULL     DEFAULT '0',
  `is_recursive`                  TINYINT(1) NOT NULL     DEFAULT '0',
  `plugin_databases_databases_id` INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_plugin_databases_databases (id)',
  `name`                          VARCHAR(255)
                                  COLLATE utf8_unicode_ci DEFAULT NULL,
  `port`                          INT(11)    NOT NULL     DEFAULT '0',
  `path`                          VARCHAR(255)
                                  COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment`                       TEXT COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `plugin_databases_databases_id` (`plugin_databases_databases_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_databases_scripts`;
CREATE TABLE `glpi_plugin_databases_scripts` (
  `id`                              INT(11)    NOT NULL     AUTO_INCREMENT,
  `entities_id`                     INT(11)    NOT NULL     DEFAULT '0',
  `is_recursive`                    TINYINT(1) NOT NULL     DEFAULT '0',
  `plugin_databases_databases_id`   INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_plugin_databases_databases (id)',
  `plugin_databases_scripttypes_id` INT(11)    NOT NULL     DEFAULT '0'
  COMMENT 'RELATION to glpi_plugin_databases_scripttypes (id)',
  `name`                            VARCHAR(255)
                                    COLLATE utf8_unicode_ci DEFAULT NULL,
  `port`                            INT(11)    NOT NULL     DEFAULT '0',
  `path`                            VARCHAR(255)
                                    COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment`                         TEXT COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `plugin_databases_databases_id` (`plugin_databases_databases_id`),
  KEY `plugin_databases_scripttypes_id` (`plugin_databases_scripttypes_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_databases_databases_items`;
CREATE TABLE `glpi_plugin_databases_databases_items` (
  `id`                            INT(11)                 NOT NULL AUTO_INCREMENT,
  `plugin_databases_databases_id` INT(11)                 NOT NULL DEFAULT '0'
  COMMENT 'RELATION to glpi_plugin_databases_databases (id)',
  `items_id`                      INT(11)                 NOT NULL DEFAULT '0'
  COMMENT 'RELATION to various tables, according to itemtype (id)',
  `itemtype`                      VARCHAR(100)
                                  COLLATE utf8_unicode_ci NOT NULL
  COMMENT 'see .class.php file',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unicity` (`plugin_databases_databases_id`, `items_id`, `itemtype`),
  KEY `FK_device` (`items_id`, `itemtype`),
  KEY `item` (`itemtype`, `items_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

INSERT INTO `glpi_displaypreferences` VALUES (NULL, 'PluginDatabasesDatabase', '2', '2', '0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL, 'PluginDatabasesDatabase', '6', '3', '0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL, 'PluginDatabasesDatabase', '7', '4', '0');