ALTER TABLE `glpi_plugin_manageentities_configs`
   ADD `linktocontract` tinyint(1) NOT NULL default '0' COMMENT 'default for no';

ALTER TABLE `glpi_plugin_manageentities_contracts`
   ADD `contract_added` tinyint(1) NOT NULL default '0';

ALTER TABLE `glpi_plugin_manageentities_contractstates`
   ADD `is_closed` tinyint(1) NOT NULL default '0';
