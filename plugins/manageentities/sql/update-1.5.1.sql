ALTER TABLE `glpi_plugin_manageentity_config` ADD `hourbyday` INT( 11 ) NOT NULL;
UPDATE `glpi_plugin_manageentity_config` SET `hourbyday`='8' WHERE `ID` = '1';