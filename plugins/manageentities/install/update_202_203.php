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
 * Update from 2.0.2 to 2.0.3
 *
 * @return bool for success (will die for most error)
 * */
function update202to203() {
   global $DB;

   $migration = new Migration(203);

   $migration->addField('glpi_plugin_manageentities_configs', 'allow_same_periods', 'bool');
   $migration->addField('glpi_plugin_manageentities_criprices', 'plugin_manageentities_contractdays_id', 'integer');
   $migration->addField('glpi_plugin_manageentities_criprices', 'is_default', 'bool');

   $migration->executeMigration();

   // UPDATE glpi_plugin_manageentities_criprices

   $check = [];

   // Default Cri type
   $query = "SELECT DISTINCT `glpi_plugin_manageentities_criprices`.`id` as criprices_id, 
                    `glpi_plugin_manageentities_criprices`.`price`, 
                    `glpi_plugin_manageentities_contractdays`.`id` as contractdays_id,
                    `glpi_plugin_manageentities_contractdays`.`plugin_manageentities_critypes_id` as critypes_id,
                    `glpi_plugin_manageentities_contractdays`.`entities_id`
             FROM `glpi_plugin_manageentities_criprices`
             INNER JOIN `glpi_plugin_manageentities_contractdays` 
               ON (`glpi_plugin_manageentities_contractdays`.`plugin_manageentities_critypes_id` = `glpi_plugin_manageentities_criprices`.`plugin_manageentities_critypes_id` 
                  AND `glpi_plugin_manageentities_contractdays`.`entities_id` = `glpi_plugin_manageentities_criprices`.`entities_id`
                  AND `glpi_plugin_manageentities_contractdays`.`plugin_manageentities_critypes_id` != 0)";

   if ($result = $DB->query($query)) {
      if ($DB->numrows($result) > 0) {
         while ($data = $DB->fetchAssoc($result)) {
            if (isset($check[$data['contractdays_id']]) && in_array($data['critypes_id'], $check[$data['contractdays_id']])) {
               continue;
            }
            $query = "INSERT INTO `glpi_plugin_manageentities_criprices`
                            (`entities_id` ,`plugin_manageentities_critypes_id` ,`price` ,`plugin_manageentities_contractdays_id`, `is_default`)
                            VALUES ('" . $data['entities_id'] . "', '" . $data['critypes_id'] . "', '" . $data['price'] . "', '" . $data['contractdays_id'] . "', '1')";
            $DB->query($query);
            $check[$data['contractdays_id']][] = $data['critypes_id'];
         }
      }
   }

   $check2 = [];

   // Cridetail cri type
   $query = "SELECT DISTINCT `glpi_plugin_manageentities_criprices`.`id` as criprices_id, 
                    `glpi_plugin_manageentities_criprices`.`price`, 
                    `glpi_plugin_manageentities_cridetails`.`plugin_manageentities_contractdays_id` as contractdays_id,
                    `glpi_plugin_manageentities_cridetails`.`plugin_manageentities_critypes_id` as critypes_id,
                    `glpi_plugin_manageentities_cridetails`.`entities_id`
             FROM `glpi_plugin_manageentities_criprices`
             INNER JOIN `glpi_plugin_manageentities_cridetails` 
               ON (`glpi_plugin_manageentities_cridetails`.`plugin_manageentities_critypes_id` = `glpi_plugin_manageentities_criprices`.`plugin_manageentities_critypes_id` 
                  AND `glpi_plugin_manageentities_cridetails`.`entities_id` = `glpi_plugin_manageentities_criprices`.`entities_id`
                  AND `glpi_plugin_manageentities_cridetails`.`plugin_manageentities_contractdays_id` != 0)";

   if ($result = $DB->query($query)) {
      if ($DB->numrows($result) > 0) {
         while ($data = $DB->fetchAssoc($result)) {
            if (isset($check[$data['contractdays_id']]) && in_array($data['critypes_id'], $check[$data['contractdays_id']])) {
               continue;
            }
            if (isset($check2[$data['contractdays_id']]) && in_array($data['critypes_id'], $check2[$data['contractdays_id']])) {
               continue;
            }
            $query = "INSERT INTO `glpi_plugin_manageentities_criprices`
                      (`entities_id` ,`plugin_manageentities_critypes_id` ,`price` ,`plugin_manageentities_contractdays_id`, `is_default`)
                      VALUES ('" . $data['entities_id'] . "', '" . $data['critypes_id'] . "', '" . $data['price'] . "', '" . $data['contractdays_id'] . "', '0')";
            $DB->query($query);
            $check2[$data['contractdays_id']][] = $data['critypes_id'];
         }
      }
   }

   // CLEAN glpi_plugin_manageentities_criprices
   $query = "DELETE FROM `glpi_plugin_manageentities_criprices` WHERE `plugin_manageentities_contractdays_id` = 0;";
   $DB->query($query);

   return true;
}