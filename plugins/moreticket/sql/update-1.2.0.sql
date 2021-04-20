ALTER TABLE glpi_plugin_moreticket_configs
  ADD `date_report_mandatory` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE glpi_plugin_moreticket_configs
  ADD `waitingtype_mandatory` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE glpi_plugin_moreticket_configs
  ADD `solutiontype_mandatory` TINYINT(1) NOT NULL DEFAULT '0';
