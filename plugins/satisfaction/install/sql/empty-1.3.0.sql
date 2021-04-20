CREATE TABLE `glpi_plugin_satisfaction_surveys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) NOT NULL DEFAULT 0,
  `is_recursive` tinyint(1) NOT NULL default '0',
  `is_active` tinyint(1) NOT NULL default '0',
  `name` varchar(255) collate utf8_unicode_ci default NULL,
  `comment` text collate utf8_unicode_ci default NULL,
  `date_creation` date default NULL,
  `date_mod` datetime default NULL,
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