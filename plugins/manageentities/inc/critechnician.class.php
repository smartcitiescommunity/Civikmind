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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginManageentitiesCriTechnician extends CommonDBTM {

   function checkIfTechnicianExists($ID) {
      global $DB;

      $result = $DB->query("SELECT `id`
                FROM `" . $this->getTable() . "`
                WHERE `tickets_id` = '" . $ID . "' ");
      if ($DB->numrows($result) > 0)
         return $DB->result($result, 0, "id");
      else
         return 0;
   }

   function addDefaultTechnician($user_id, $ID) {

      $input["users_id"]   = $user_id;
      $input["tickets_id"] = $ID;

      return $this->add($input);
   }

   function getTechnicians($tickets_id, $remove_tag = false) {
      global $DB;

      $dbu    = new DbUtils();
      $techs  = [];
      $query  = "SELECT `users_id_tech` as users_id,
                       `glpi_users`.`name`,
                       `glpi_users`.`realname`,
                       `glpi_users`.`firstname`
               FROM `glpi_tickettasks`
               LEFT JOIN `glpi_users`
                 ON(`glpi_users`.`id`=`glpi_tickettasks`.`users_id_tech`)
               WHERE `tickets_id` = '" . $tickets_id . "'";
      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetchArray($result)) {
            if ($data['users_id'] != 0) {
               if ($remove_tag) {
                  $techs['notremove'][$data['users_id']] = $dbu->formatUserName($data["users_id"],
                                                                                $data["name"], $data["realname"], $data["firstname"], 0);
               } else {
                  $techs[$data['users_id']] = $dbu->formatUserName($data["users_id"],
                                                                   $data["name"], $data["realname"], $data["firstname"], 0);
               }
            }
         }
      }

      $query  = "SELECT `users_id` as users_id,
                       `glpi_users`.`name`,
                       `glpi_users`.`realname`,
                       `glpi_users`.`firstname`
               FROM `glpi_plugin_manageentities_critechnicians`
               LEFT JOIN `glpi_users`
                 ON(`glpi_users`.`id`=`glpi_plugin_manageentities_critechnicians`.`users_id`)
               WHERE `tickets_id` = '" . $tickets_id . "' ";
      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetchArray($result)) {
            if ($data['users_id'] != 0 && !isset($techs['notremove'][$data['users_id']])) {
               if ($remove_tag) {
                  $techs['remove'][$data['users_id']] = $dbu->formatUserName($data["users_id"],
                                                                             $data["name"], $data["realname"], $data["firstname"], 0);
               } else {
                  $techs[$data['users_id']] = $dbu->formatUserName($data["users_id"],
                                                                   $data["name"], $data["realname"], $data["firstname"], 0);
               }
            }
         }
      }

      return $techs;
   }
}