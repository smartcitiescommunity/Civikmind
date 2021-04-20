<?php

/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Manageentities plugin for GLPI
 Copyright (C) 2014-2017 by the Manageentities Development Team.

 https://github.com/InfotelGLPI/manageentities
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Manageentities.

 Manageentities is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Manageentities is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Manageentities. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/**
 * Update from 2.1.0 to 2.1.1
 *
 * @return bool for success (will die for most error)
 * */
function update210to211() {
   global $DB;

   $migration = new Migration(211);

   $migration->addField('glpi_plugin_manageentities_configs', 'choice_intervention', 'integer', array('value' => NULL));
   $migration->addField('glpi_plugin_manageentities_configs', 'contract_states', 'text', array('value' => NULL));
   $migration->addField('glpi_plugin_manageentities_configs', 'business_id', 'text', array('value' => NULL));

   $migration->addField('glpi_plugin_manageentities_preferences', 'contract_states', 'text', array('value' => NULL));
   $migration->addField('glpi_plugin_manageentities_preferences', 'business_id', 'text', array('value' => NULL));
   $migration->addField('glpi_plugin_manageentities_preferences', 'companies_id', 'text', array('value' => NULL));

   $migration->addField('glpi_plugin_manageentities_contractdays', 'comment', 'text');
   $migration->addField('glpi_plugin_manageentities_contracts', 'refacturable_costs', 'bool', array('value' => '0'));


   $query_businesscontacts = "
            CREATE TABLE IF NOT EXISTS `glpi_plugin_manageentities_businesscontacts` (
               `id` int(11) NOT NULL auto_increment,
               `users_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
               `entities_id` int(11) NOT NULL default '0',
               `is_default` tinyint(1) NOT NULL default '0',
               PRIMARY KEY  (`id`),
               UNIQUE KEY `unicity` (`users_id`,`entities_id`),
               KEY `users_id` (`users_id`),
               KEY `entities_id` (`entities_id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
   $DB->queryOrDie($query_businesscontacts, "ADD glpi_plugin_manageentities_businesscontacts");


   $query_companies = "
            CREATE TABLE IF NOT EXISTS `glpi_plugin_manageentities_companies` (
               `id` int(11) NOT NULL auto_increment,
               `name` varchar(255) collate utf8_unicode_ci default NULL,
               `address` text collate utf8_unicode_ci COMMENT 'address of the company shown on CRI',
               `entity_id` text default NULL,
               `recursive` int(11) default 0,
               `logo_id` int(11) default 0 COMMENT 'RELATION to glpi_documents',
               PRIMARY KEY  (`id`),
               KEY `logo_id` (`logo_id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
   $DB->queryOrDie($query_companies, "ADD glpi_plugin_manageentities_companies");

   if ($DB->fieldExists("glpi_plugin_manageentities_configs", "company_address")) {
      $dbu   = new DbUtils();
      $datas = $dbu->getAllDataFromTable("glpi_plugin_manageentities_configs");
      $data  = reset($datas);
      $DB->queryOrDie("INSERT INTO `glpi_plugin_manageentities_companies`(`address`, `entity_id`, `recursive`) VALUES ('" . $data['company_address'] . "', 0, 1)", "Migration company_address");

      $migration->dropField("glpi_plugin_manageentities_configs", "company_address");
   }


   $migration->executeMigration();

   return true;
}