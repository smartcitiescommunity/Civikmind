DROP TABLE IF EXISTS `glpi_plugin_badges_badges`;
CREATE TABLE `glpi_plugin_badges_badges` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL default '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `serial` varchar(255) collate utf8_unicode_ci default NULL,
   `plugin_badges_badgetypes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_badges_badgetypes (id)',
   `locations_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_locations (id)',
   `date_affectation` date default NULL,
   `date_expiration` date default NULL,
   `states_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_states (id)',
   `users_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
   `is_helpdesk_visible` int(11) NOT NULL default '1',
   `date_mod` datetime default NULL,
   `comment` text collate utf8_unicode_ci,
   `notepad` longtext collate utf8_unicode_ci,
   `is_deleted` tinyint(1) NOT NULL default '0',
        `is_bookable` tinyint(1) NOT NULL default '1',
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `is_recursive` (`is_recursive`),
   KEY `plugin_badges_badgetypes_id` (`plugin_badges_badgetypes_id`),
   KEY `locations_id` (`locations_id`),
   KEY `date_expiration` (`date_expiration`),
   KEY `states_id` (`states_id`),
   KEY `users_id` (`users_id`),
   KEY `is_helpdesk_visible` (`is_helpdesk_visible`),
   KEY `is_deleted` (`is_deleted`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_badges_badgetypes`;
   CREATE TABLE `glpi_plugin_badges_badgetypes` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   `is_recursive` tinyint(1) NOT NULL default '0',
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_badges_profiles`;
CREATE TABLE `glpi_plugin_badges_profiles` (
   `id` int(11) NOT NULL auto_increment,
   `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
   `badges` char(1) collate utf8_unicode_ci default NULL,
   `open_ticket` char(1) collate utf8_unicode_ci default NULL,
   PRIMARY KEY  (`id`),
   KEY `profiles_id` (`profiles_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_badges_notificationstates`;
CREATE TABLE `glpi_plugin_badges_notificationstates` (
   `id` int(11) NOT NULL auto_increment,
   `states_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_states (id)',
   PRIMARY KEY  (`id`),
   KEY `states_id` (`states_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_badges_configs`;
CREATE TABLE `glpi_plugin_badges_configs` (
   `id` int(11) NOT NULL auto_increment,
   `delay_expired` varchar(50) collate utf8_unicode_ci NOT NULL default '30',
   `delay_whichexpire` varchar(50) collate utf8_unicode_ci NOT NULL default '30',
   `delay_returnexpire` int(11) NOT NULL default '0',
   PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_badges_requests`;
CREATE TABLE `glpi_plugin_badges_requests` (
   `id` int(11) NOT NULL auto_increment,
   `badges_id`  int(11) NOT NULL default '0',
   `requesters_id`  int(11) NOT NULL default '0',
   `visitor_firstname` varchar(255) collate utf8_unicode_ci default NULL,
   `visitor_realname` varchar(255) collate utf8_unicode_ci default NULL,
   `visitor_society` varchar(255) collate utf8_unicode_ci default NULL,
   `affectation_date` datetime default NULL,
   `return_date` datetime default NULL,
   `is_affected`  tinyint(1) NOT NULL default '0',
   PRIMARY KEY  (`id`),
        KEY `badges_id` (`badges_id`),
        KEY `requesters_id` (`requesters_id`),
        KEY `is_affected` (`is_affected`),
        KEY `affectation_date` (`affectation_date`),
        KEY `return_date` (`return_date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_badges_configs` VALUES (1, '30', '30', '30');

INSERT INTO `glpi_notificationtemplates` (name, itemtype)
VALUES('Alert Badges', 'PluginBadgesBadge');

INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginBadgesBadge','3','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginBadgesBadge','4','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginBadgesBadge','5','4','0');