ALTER TABLE `glpi_plugin_resources_ranks`
   ADD `begin_date` date default NULL,
   ADD `end_date` date default NULL;

ALTER TABLE `glpi_plugin_resources_professions`
   ADD `begin_date` date default NULL,
   ADD `end_date` date default NULL;