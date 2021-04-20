ALTER TABLE glpi_plugin_metademands_metademands ADD `is_order` tinyint(1) default 0;
ALTER TABLE glpi_plugin_metademands_metademands ADD `date_creation` datetime DEFAULT NULL;
ALTER TABLE glpi_plugin_metademands_metademands ADD `date_mod` datetime DEFAULT NULL;
ALTER TABLE glpi_plugin_metademands_fields ADD `date_creation` datetime DEFAULT NULL;
ALTER TABLE glpi_plugin_metademands_fields ADD `date_mod` datetime DEFAULT NULL;
ALTER TABLE glpi_plugin_metademands_fields ADD `is_basket` tinyint(1) default 0;

ALTER TABLE `glpi_plugin_metademands_fields` CHANGE `fields_display` `hidden_link` VARCHAR(255) NOT NULL;
ALTER TABLE `glpi_plugin_metademands_fields` CHANGE `plugin_metademands_tasks_id` `plugin_metademands_tasks_id` VARCHAR(255) DEFAULT NULL;
ALTER TABLE `glpi_plugin_metademands_tickets_metademands` ADD `tickettemplates_id` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `glpi_plugin_metademands_fields` ADD `max_upload` INT(11) NOT NULL DEFAULT 0;
ALTER TABLE `glpi_plugin_metademands_fields` ADD `regex` VARCHAR(255) NOT NULL DEFAULT '';

CREATE TABLE `glpi_plugin_metademands_basketlines` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `users_id` int(11) NOT NULL default '0',
    `plugin_metademands_metademands_id` int(11) NOT NULL default '0',
    `plugin_metademands_fields_id` int(11) NOT NULL default '0',
    `line` int(11) NOT NULL default '0',
    `name` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
    `value` text COLLATE utf8_unicode_ci,
    `value2` text COLLATE utf8_unicode_ci,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unicity` (`plugin_metademands_metademands_id`,`plugin_metademands_fields_id`,`line`,`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE glpi_plugin_metademands_tasks DROP FOREIGN KEY glpi_plugin_metademands_tasks_ibfk_1;
ALTER TABLE glpi_plugin_metademands_tickets_metademands DROP FOREIGN KEY glpi_plugin_metademands_tickets_metademands_ibfk_1;
ALTER TABLE glpi_plugin_metademands_tickets_tasks DROP FOREIGN KEY glpi_plugin_metademands_tickets_tasks_ibfk_1;