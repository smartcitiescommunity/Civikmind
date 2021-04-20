ALTER TABLE `glpi_plugin_sgbd`
  ADD `recursive` TINYINT(1) NOT NULL DEFAULT '0'
  AFTER `FK_entities`;

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

ALTER TABLE `glpi_plugin_sgbd_device`
  DROP INDEX `FK_compte`,
  ADD UNIQUE `FK_sgbd` (`FK_sgbd`, `FK_device`, `device_type`);