ALTER TABLE glpi_plugin_metademands_metademands ADD `create_one_ticket` tinyint(1) default 0;
ALTER TABLE glpi_plugin_metademands_basketlines DROP KEY `unicity`;
ALTER TABLE glpi_plugin_metademands_basketlines ADD KEY `unicity` (`plugin_metademands_metademands_id`,`plugin_metademands_fields_id`,`line`,`name`,`users_id`);