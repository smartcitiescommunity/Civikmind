CREATE TABLE `glpi_plugin_activity_holidaycounts` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
   `date_mod` DATETIME NULL default NULL,
   `count` decimal(20,1) NOT NULL DEFAULT '0.00',
   `plugin_activity_holidayperiods_id` int(4) NOT NULL default '0',
   `plugin_activity_holidaytypes_id` int(4) NOT NULL default '0',
   `users_id` int(11) NOT NULL default '0',
   PRIMARY KEY  (`id`),
   KEY `users_id` (`users_id`),
   KEY `plugin_activity_holidayperiods_id` (`plugin_activity_holidayperiods_id`),
   KEY `plugin_activity_holidaytypes_id` (`plugin_activity_holidaytypes_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `glpi_plugin_activity_holidayperiods` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
   `short_name` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   `begin_date` DATE NULL default NULL,
   `end_date`  DATE NULL default NULL,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;