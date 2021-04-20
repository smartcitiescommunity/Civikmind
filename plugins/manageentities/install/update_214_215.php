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
 * Update from 2.1.4 to 2.1.5
 *
 * @return bool for success (will die for most error)
 * */
function update214to215() {
   global $DB;

   $migration = new Migration(215);
   $migration->addField('glpi_plugin_manageentities_contractdays', 'contract_type', 'tinyint(1)', array('value' => '0'));
   $migration->executeMigration();

   $query  = " SELECT * FROM `glpi_plugin_manageentities_contracts`";
   if ($result = $DB->query($query)) {
      if ($DB->numrows($result) > 0) {
         while ($data = $DB->fetchAssoc($result)) {

            $query_contractdays  = "SELECT * FROM `glpi_plugin_manageentities_contractdays` WHERE `contracts_id` = " . $data['contracts_id'].";";
            if ($result_contractdays = $DB->query($query_contractdays)) {
               if ($DB->numrows($result_contractdays) > 0) {
                  while ($data_contractdays = $DB->fetchAssoc($result_contractdays)) {
                     $query = "UPDATE `glpi_plugin_manageentities_contractdays` SET `contract_type` = " . $data['contract_type'].";";
                     $DB->query($query);
                  }
               }
            }
         }
      }
   }

   $migration->executeMigration();

   return true;
}

?>