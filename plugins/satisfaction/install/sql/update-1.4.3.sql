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

CREATE TABLE `glpi_plugin_satisfaction_reminders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL DEFAULT '0',
  `tickets_id` int(11) NOT NULL,
  `date` date default NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;