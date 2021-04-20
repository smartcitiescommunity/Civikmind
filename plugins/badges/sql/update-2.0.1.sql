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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `glpi_plugin_badges_configs` ADD `delay_returnexpire` int(11) NOT NULL default '0';
ALTER TABLE `glpi_plugin_badges_badges` ADD `is_bookable` tinyint(1) NOT NULL default '1';