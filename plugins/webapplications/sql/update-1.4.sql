ALTER TABLE `glpi_plugin_appweb`
  ADD `recursive` TINYINT(1) NOT NULL DEFAULT '0'
  AFTER `FK_entities`;

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