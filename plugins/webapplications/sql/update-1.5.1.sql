ALTER TABLE `glpi_plugin_appweb`
  ADD `helpdesk_visible` INT(11) NOT NULL DEFAULT '1',
  ADD `date_mod` DATETIME DEFAULT NULL;