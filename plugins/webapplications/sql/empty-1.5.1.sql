DROP TABLE IF EXISTS `glpi_plugin_appweb`;
CREATE TABLE `glpi_plugin_appweb` (
  `ID`                 INT(11)                 NOT NULL AUTO_INCREMENT,
  `FK_entities`        INT(11)                 NOT NULL DEFAULT '0',
  `recursive`          TINYINT(1)              NOT NULL DEFAULT '0',
  `name`               VARCHAR(255)
                       COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `address`            VARCHAR(255)
                       COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `type`               INT(4)                  NOT NULL DEFAULT '0',
  `server`             INT(4)                  NOT NULL DEFAULT '0',
  `technic`            INT(4)                  NOT NULL DEFAULT '0',
  `version`            VARCHAR(255)
                       COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `FK_users`           INT(4)                  NOT NULL,
  `FK_groups`          INT(11)                 NOT NULL DEFAULT '0',
  `FK_enterprise`      SMALLINT(6)             NOT NULL DEFAULT '0',
  `FK_glpi_enterprise` SMALLINT(6)             NOT NULL DEFAULT '0',
  `location`           INT(4)                  NOT NULL DEFAULT '0',
  `date_mod`           DATETIME                         DEFAULT NULL,
  `helpdesk_visible`   INT(11)                 NOT NULL DEFAULT '1',
  `notes`              LONGTEXT,
  `comment`            VARCHAR(255)
                       COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `deleted`            SMALLINT(6)             NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_dropdown_plugin_appweb_type`;
CREATE TABLE `glpi_dropdown_plugin_appweb_type` (
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

DROP TABLE IF EXISTS `glpi_dropdown_plugin_appweb_server_type`;
CREATE TABLE `glpi_dropdown_plugin_appweb_server_type` (
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

INSERT INTO `glpi_dropdown_plugin_appweb_server_type` (`ID`, `name`, `comments`) VALUES ('1', 'Apache', '');
INSERT INTO `glpi_dropdown_plugin_appweb_server_type` (`ID`, `name`, `comments`) VALUES ('2', 'IIS', '');
INSERT INTO `glpi_dropdown_plugin_appweb_server_type` (`ID`, `name`, `comments`) VALUES ('3', 'Tomcat', '');

DROP TABLE IF EXISTS `glpi_dropdown_plugin_appweb_technic`;
CREATE TABLE `glpi_dropdown_plugin_appweb_technic` (
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

INSERT INTO `glpi_dropdown_plugin_appweb_technic` (`ID`, `name`, `comments`) VALUES ('1', 'Asp', '');
INSERT INTO `glpi_dropdown_plugin_appweb_technic` (`ID`, `name`, `comments`) VALUES ('2', 'Cgi', '');
INSERT INTO `glpi_dropdown_plugin_appweb_technic` (`ID`, `name`, `comments`) VALUES ('3', 'Java', '');
INSERT INTO `glpi_dropdown_plugin_appweb_technic` (`ID`, `name`, `comments`) VALUES ('4', 'Perl', '');
INSERT INTO `glpi_dropdown_plugin_appweb_technic` (`ID`, `name`, `comments`) VALUES ('5', 'Php', '');
INSERT INTO `glpi_dropdown_plugin_appweb_technic` (`ID`, `name`, `comments`) VALUES ('6', '.Net', '');

DROP TABLE IF EXISTS `glpi_dropdown_plugin_appweb_protocol`;
CREATE TABLE `glpi_dropdown_plugin_appweb_protocol` (
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

INSERT INTO `glpi_dropdown_plugin_appweb_protocol` (`ID`, `name`, `comments`) VALUES ('1', 'http', '');
INSERT INTO `glpi_dropdown_plugin_appweb_protocol` (`ID`, `name`, `comments`) VALUES ('2', 'https', '');

DROP TABLE IF EXISTS `glpi_plugin_appweb_device`;
CREATE TABLE `glpi_plugin_appweb_device` (
  `ID`          INT(11) NOT NULL AUTO_INCREMENT,
  `FK_appweb`   INT(11) NOT NULL DEFAULT '0',
  `FK_device`   INT(11) NOT NULL DEFAULT '0',
  `device_type` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `FK_appweb` (`FK_appweb`, `FK_device`, `device_type`),
  KEY `FK_appweb_2` (`FK_appweb`),
  KEY `FK_device` (`FK_device`, `device_type`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_appweb_profiles`;
CREATE TABLE `glpi_plugin_appweb_profiles` (
  `ID`          INT(11) NOT NULL        AUTO_INCREMENT,
  `name`        VARCHAR(255)
                COLLATE utf8_unicode_ci DEFAULT NULL,
  `appweb`      CHAR(1)                 DEFAULT NULL,
  `open_ticket` CHAR(1)                 DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `name` (`name`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

INSERT INTO `glpi_display` (`ID`, `type`, `num`, `rank`, `FK_users`) VALUES (NULL, '1300', '2', '2', '0');
INSERT INTO `glpi_display` (`ID`, `type`, `num`, `rank`, `FK_users`) VALUES (NULL, '1300', '3', '4', '0');
INSERT INTO `glpi_display` (`ID`, `type`, `num`, `rank`, `FK_users`) VALUES (NULL, '1300', '6', '5', '0');
INSERT INTO `glpi_display` (`ID`, `type`, `num`, `rank`, `FK_users`) VALUES (NULL, '1300', '7', '6', '0');
INSERT INTO `glpi_display` (`ID`, `type`, `num`, `rank`, `FK_users`) VALUES (NULL, '1300', '8', '7', '0');