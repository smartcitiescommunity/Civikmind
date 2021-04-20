DROP TABLE IF EXISTS `glpi_plugin_sgbd`;
CREATE TABLE `glpi_plugin_sgbd` (
  `ID`                 INT(11)                 NOT NULL AUTO_INCREMENT,
  `FK_entities`        INT(11)                 NOT NULL DEFAULT '0',
  `recursive`          TINYINT(1)              NOT NULL DEFAULT '0',
  `name`               VARCHAR(255)
                       COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `category`           INT(4)                  NOT NULL DEFAULT '0',
  `type`               INT(4)                  NOT NULL DEFAULT '0',
  `FK_users`           INT(4)                  NOT NULL DEFAULT '0',
  `FK_groups`          INT(11)                 NOT NULL DEFAULT '0',
  `server`             INT(4)                  NOT NULL DEFAULT '0',
  `FK_enterprise`      SMALLINT(6)             NOT NULL DEFAULT '0',
  `FK_glpi_enterprise` SMALLINT(6)             NOT NULL DEFAULT '0',
  `location`           INT(4)                  NOT NULL DEFAULT '0',
  `notes`              LONGTEXT,
  `comment`            VARCHAR(255)
                       COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `helpdesk_visible`   INT(11)                 NOT NULL DEFAULT '1',
  `date_mod`           DATETIME                         DEFAULT NULL,
  `deleted`            SMALLINT(6)             NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_dropdown_plugin_sgbd_type`;
CREATE TABLE `glpi_dropdown_plugin_sgbd_type` (
  `ID`          INT(11)                 NOT NULL AUTO_INCREMENT,
  `FK_entities` INT(11)                 NOT NULL DEFAULT '0',
  `name`        VARCHAR(255)
                COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `comments`    TEXT,
  PRIMARY KEY (`ID`),
  KEY `name` (`name`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_dropdown_plugin_sgbd_category`;
CREATE TABLE `glpi_dropdown_plugin_sgbd_category` (
  `ID`          INT(11)                 NOT NULL AUTO_INCREMENT,
  `FK_entities` INT(11)                 NOT NULL DEFAULT '0',
  `name`        VARCHAR(255)
                COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `comments`    TEXT,
  PRIMARY KEY (`ID`),
  KEY `name` (`name`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_dropdown_plugin_sgbd_server_type`;
CREATE TABLE `glpi_dropdown_plugin_sgbd_server_type` (
  `ID`       INT(11)                 NOT NULL AUTO_INCREMENT,
  `name`     VARCHAR(255)
             COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `comments` TEXT,
  PRIMARY KEY (`ID`),
  KEY `name` (`name`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

INSERT INTO `glpi_dropdown_plugin_sgbd_server_type` (`ID`, `name`, `comments`) VALUES ('1', 'Mysql', '');
INSERT INTO `glpi_dropdown_plugin_sgbd_server_type` (`ID`, `name`, `comments`) VALUES ('2', 'Oracle', '');
INSERT INTO `glpi_dropdown_plugin_sgbd_server_type` (`ID`, `name`, `comments`) VALUES ('3', 'SQL', '');

DROP TABLE IF EXISTS `glpi_dropdown_plugin_sgbd_script_type`;
CREATE TABLE `glpi_dropdown_plugin_sgbd_script_type` (
  `ID`       INT(11)                 NOT NULL AUTO_INCREMENT,
  `name`     VARCHAR(255)
             COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `comments` TEXT,
  PRIMARY KEY (`ID`),
  KEY `name` (`name`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_sgbd_instances`;
CREATE TABLE `glpi_plugin_sgbd_instances` (
  `ID`      INT(11)                 NOT NULL AUTO_INCREMENT,
  `FK_sgbd` INT(11)                 NOT NULL DEFAULT '0',
  `name`    VARCHAR(255)
            COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `port`    INT(11)                 NOT NULL DEFAULT '0',
  `path`    VARCHAR(255)
            COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_sgbd_scripts`;
CREATE TABLE `glpi_plugin_sgbd_scripts` (
  `ID`      INT(11)                 NOT NULL AUTO_INCREMENT,
  `FK_sgbd` INT(11)                 NOT NULL DEFAULT '0',
  `name`    VARCHAR(255)
            COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `path`    VARCHAR(255)
            COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `type`    INT(11)                 NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_sgbd_device`;
CREATE TABLE `glpi_plugin_sgbd_device` (
  `ID`          INT(11) NOT NULL AUTO_INCREMENT,
  `FK_sgbd`     INT(11) NOT NULL DEFAULT '0',
  `FK_device`   INT(11) NOT NULL DEFAULT '0',
  `device_type` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `FK_sgbd` (`FK_sgbd`, `FK_device`, `device_type`),
  KEY `FK_sgbd_2` (`FK_sgbd`),
  KEY `FK_device` (`FK_device`, `device_type`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_sgbd_profiles`;
CREATE TABLE `glpi_plugin_sgbd_profiles` (
  `ID`          INT(11) NOT NULL        AUTO_INCREMENT,
  `name`        VARCHAR(255)
                COLLATE utf8_unicode_ci DEFAULT NULL,
  `sgbd`        CHAR(1)                 DEFAULT NULL,
  `open_ticket` CHAR(1)                 DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `name` (`name`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

INSERT INTO `glpi_display` (`ID`, `type`, `num`, `rank`, `FK_users`) VALUES (NULL, '2400', '2', '2', '0');
INSERT INTO `glpi_display` (`ID`, `type`, `num`, `rank`, `FK_users`) VALUES (NULL, '2400', '6', '3', '0');
INSERT INTO `glpi_display` (`ID`, `type`, `num`, `rank`, `FK_users`) VALUES (NULL, '2400', '7', '4', '0');