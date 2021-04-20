ALTER TABLE `glpi_plugin_tasklists_preferences`
    ADD `automatic_refresh`       TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `glpi_plugin_tasklists_preferences`
    ADD `automatic_refresh_delay` INT(11)    NOT NULL DEFAULT '10';

