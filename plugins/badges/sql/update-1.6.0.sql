ALTER TABLE `glpi_plugin_badges` RENAME `glpi_plugin_badges_badges`;
ALTER TABLE `glpi_dropdown_plugin_badges_type` RENAME `glpi_plugin_badges_badgetypes`;
ALTER TABLE `glpi_plugin_badges_default` RENAME `glpi_plugin_badges_notificationstates`;
ALTER TABLE `glpi_plugin_badges_config` RENAME `glpi_plugin_badges_configs`;
DROP TABLE IF EXISTS `glpi_plugin_badges_mailing`;


UPDATE `glpi_plugin_badges_badges` SET `FK_users` = '0' WHERE `FK_users` IS NULL;


ALTER TABLE `glpi_plugin_badges_badges` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `type` `plugin_badges_badgetypes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_badges_badgetypes (id)',
   CHANGE `location` `locations_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_locations (id)',
   CHANGE `date_affect` `date_affectation` date default NULL,
   CHANGE `date_expiration` `date_expiration` date default NULL,
   CHANGE `state` `states_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_states (id)',
   CHANGE `FK_users` `users_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
   CHANGE `helpdesk_visible` `is_helpdesk_visible` int(11) NOT NULL default '1',
   CHANGE `notes` `notepad` longtext collate utf8_unicode_ci,
   CHANGE `comments` `comment` text collate utf8_unicode_ci,
   CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL default '0',
   ADD INDEX (`plugin_badges_badgetypes_id`),
   ADD INDEX (`users_id`),
   ADD INDEX (`locations_id`),
   ADD INDEX (`date_mod`),
   ADD INDEX (`is_helpdesk_visible`);

ALTER TABLE `glpi_plugin_badges_badgetypes` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `comments` `comment` text collate utf8_unicode_ci;

ALTER TABLE `glpi_plugin_badges_profiles` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   ADD `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
   CHANGE `badges` `badges` char(1) collate utf8_unicode_ci default NULL,
   CHANGE `open_ticket` `open_ticket` char(1) collate utf8_unicode_ci default NULL,
   ADD INDEX (`profiles_id`);

ALTER TABLE `glpi_plugin_badges_notificationstates`
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `status` `states_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_states (id)';
   
ALTER TABLE `glpi_plugin_badges_configs` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `delay` `delay_expired` varchar(50) collate utf8_unicode_ci NOT NULL default '30',
   ADD `delay_whichexpire` varchar(50) collate utf8_unicode_ci NOT NULL default '30';
   
INSERT INTO `glpi_notificationtemplates` VALUES(NULL, 'Alert Badges', 'PluginBadgesBadge', '2010-02-23 23:44:46','',NULL);