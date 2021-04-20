DROP TABLE IF EXISTS `glpi_plugin_webapplications_appliances`;
CREATE TABLE `glpi_plugin_webapplications_appliances` (
   `id` int(11) NOT NULL auto_increment,
   `appliances_id` int(11) NOT NULL,
   `webapplicationtypes_id`       INT(11)    NOT NULL     DEFAULT '0'
        COMMENT 'RELATION to glpi_plugin_webapplications_webapplicationtypes (id)',
   `webapplicationservertypes_id` INT(11)    NOT NULL     DEFAULT '0'
        COMMENT 'RELATION to glpi_plugin_webapplications_webapplicationservertypes (id)',
   `webapplicationtechnics_id`    INT(11)    NOT NULL     DEFAULT '0'
        COMMENT 'RELATION to glpi_plugin_webapplications_webapplicationtechnics (id)',
   `address` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
   `backoffice`  VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
   PRIMARY KEY  (`id`),
   KEY `appliances_id` (`appliances_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
