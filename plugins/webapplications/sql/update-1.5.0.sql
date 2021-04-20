ALTER TABLE `glpi_plugin_appweb_profiles`
  DROP COLUMN `interface`,
  DROP COLUMN `is_default`;
ALTER TABLE `glpi_plugin_appweb`
  DROP COLUMN `target`,
  DROP COLUMN `link_name`,
  DROP COLUMN `port`,
  DROP COLUMN `protocol`;
ALTER TABLE `glpi_plugin_appweb`
  ADD `FK_groups` INT(11) NOT NULL DEFAULT '0';
DROP TABLE `glpi_dropdown_plugin_appweb_protocol`;
ALTER TABLE `glpi_plugin_appweb`
  ADD `FK_users` INT(4);
ALTER TABLE `glpi_plugin_appweb_profiles`
  ADD `open_ticket` CHAR(1) DEFAULT NULL;