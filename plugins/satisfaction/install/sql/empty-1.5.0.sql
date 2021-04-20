# noinspection SqlNoDataSourceInspectionForFile

CREATE TABLE `glpi_plugin_satisfaction_surveys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) NOT NULL DEFAULT 0,
  `is_recursive` tinyint(1) NOT NULL default '0',
  `is_active` tinyint(1) NOT NULL default '0',
  `name` varchar(255) collate utf8_unicode_ci default NULL,
  `comment` text collate utf8_unicode_ci default NULL,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date_mod` timestamp NULL DEFAULT NULL,
  `reminders_days` int(11) NOT NULL default '30',
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `glpi_plugin_satisfaction_surveyquestions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_satisfaction_surveys_id` int(11) NOT NULL,
  `name` text collate utf8_unicode_ci default NULL,
  `type` varchar(255) collate utf8_unicode_ci default NULL,
  `comment` text collate utf8_unicode_ci default NULL,
  `number` int(11) NOT NULL DEFAULT 0,
  `default_value` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `glpi_plugin_satisfaction_surveyanswers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `answer` text collate utf8_unicode_ci default NULL,
  `comment` text collate utf8_unicode_ci default NULL,
  `plugin_satisfaction_surveys_id` int(11) NOT NULL,
  `ticketsatisfactions_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_satisfaction_surveytranslations`;
CREATE TABLE `glpi_plugin_satisfaction_surveytranslations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_satisfaction_surveys_id` int(11) NOT NULL DEFAULT '0',
  `glpi_plugin_satisfaction_surveyquestions_id` int(11) NOT NULL DEFAULT '0',
  `language` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unicity` (`plugin_satisfaction_surveys_id`,`glpi_plugin_satisfaction_surveyquestions_id`,`language`),
  KEY `typeid` (`plugin_satisfaction_surveys_id`,`glpi_plugin_satisfaction_surveyquestions_id`),
  KEY `language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_satisfaction_surveyreminders`;
CREATE TABLE `glpi_plugin_satisfaction_surveyreminders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_satisfaction_surveys_id` int(11) NOT NULL,
  `name` text collate utf8_unicode_ci default NULL,
  `duration_type` int(11) NOT NULL,
  `duration` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL default '0',
  `comment` text collate utf8_unicode_ci default NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_satisfaction_reminders`;
CREATE TABLE `glpi_plugin_satisfaction_reminders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL DEFAULT '0',
  `tickets_id` int(11) NOT NULL,
  `date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unicity` (`tickets_id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;