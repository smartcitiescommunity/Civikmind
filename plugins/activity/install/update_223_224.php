<?php

/*
 -------------------------------------------------------------------------
 Activity plugin for GLPI
 Copyright (C) 2019 by the Activity Development Team.
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Activity.

 Activity is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Activity is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Activity. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

/**
 * Update from 2.2.3 to 2.2.4
 *
 * @return bool for success (will die for most error)
 * */
function update223to224() {
   global $DB;

   $migration = new Migration(224);

   $query_users = "SELECT DISTINCT `users_id` 
                  FROM `glpi_plugin_activity_holidays`";

   if ($result_users = $DB->query($query_users)) {
      if ($DB->numrows($result_users) > 0) {
         while ($data_user = $DB->fetchAssoc($result_users)) {
            $user_id = $data_user['users_id'];

            //select cp
            $query_cp = "SELECT *
                        FROM `glpi_plugin_activity_holidaycounts`
                        LEFT JOIN `glpi_plugin_activity_holidayperiods`
                        ON (`glpi_plugin_activity_holidaycounts`.`plugin_activity_holidayperiods_id` = `glpi_plugin_activity_holidayperiods`.`id`)
                        WHERE `users_id`= '".$user_id."' 
                           AND `glpi_plugin_activity_holidaycounts`.`count` > 0
                           AND `glpi_plugin_activity_holidayperiods`.`short_name` LIKE 'CP';";

            $CP = [];
            if ($result_cp = $DB->query($query_cp)) {
               if ($DB->numrows($result_cp) > 0) {
                  while ($data_cp = $DB->fetchAssoc($result_cp)) {
                     $CP[$data_cp['plugin_activity_holidayperiods_id']] = ['count'                           => $data_cp['count'],
                                                                                 'name'                            => $data_cp['name'],
                                                                                 'begin'                           => $data_cp['begin'],
                                                                                 'end'                             => $data_cp['end'],
                                                                                 'plugin_activity_holidaytypes_id' => $data_cp['plugin_activity_holidaytypes_id'],
                     ];
                  }
               }
            }
            //select rtt
            $query_rtt = "SELECT *
                           FROM `glpi_plugin_activity_holidaycounts`
                           LEFT JOIN `glpi_plugin_activity_holidayperiods`
                           ON (`glpi_plugin_activity_holidaycounts`.`plugin_activity_holidayperiods_id` = `glpi_plugin_activity_holidayperiods`.`id`)
                           WHERE `users_id`= '".$user_id."' 
                              AND `glpi_plugin_activity_holidaycounts`.`count` > 0
                              AND `glpi_plugin_activity_holidayperiods`.`short_name` LIKE 'RT';";
            $RTT = [];
            if ($result_rtt = $DB->query($query_rtt)) {
               if ($DB->numrows($result_rtt) > 0) {
                  while ($data_rtt = $DB->fetchAssoc($result_rtt)) {
                     $RTT[$data_rtt['plugin_activity_holidayperiods_id']] = ['count'                           => $data_rtt['count'],
                                                                                  'name'                            => $data_rtt['name'],
                                                                                  'begin'                           => $data_rtt['begin'],
                                                                                  'end'                             => $data_rtt['end'],
                                                                                  'plugin_activity_holidaytypes_id' => $data_rtt['plugin_activity_holidaytypes_id'],
                     ];
                  }
               }
            }

            $query = "SELECT *
                     FROM `glpi_plugin_activity_holidays` 
                     WHERE `users_id` = ".$user_id." ORDER BY id";

            if ($results = $DB->query($query)) {
               if ($DB->numrows($results) > 0) {
                  while ($data = $DB->fetchAssoc($results)) {

                     $start = $data['begin'];
                     $end   = $data['end'];

                     $done = false;
                     //
                     foreach ($CP as $key_period_id => $data_cp) {
                        if (!$done && $data_cp['count'] > 0
                           && strtotime($data['begin']) >= strtotime($data_cp['begin'])
                              && strtotime($data['begin']) <= strtotime($data_cp['end'])) {
                           $done = true;
                           $CP[$key_period_id]['count'] -= 1;
                           $query_update = "UPDATE `glpi_plugin_activity_holidays` 
                              SET `plugin_activity_holidayperiods_id` = '$key_period_id' WHERE `glpi_plugin_activity_holidays`.`id` = ".$data['id'].";";
                           $DB->query($query_update);
                           break;
                        }
                     }
                     if (!$done) {
                        foreach ($RTT as $key_period_id => $data_rtt) {
                           if (!$done && $data_rtt['count'] > 0
                              && strtotime($data['begin']) >= strtotime($data_rtt['begin'])
                                 && strtotime($data['begin']) <= strtotime($data_rtt['end'])) {
                              $done = true;
                              $RTT[$key_period_id]['count'] -= 1;
                              $query_update = "UPDATE `glpi_plugin_activity_holidays` 
                              SET `plugin_activity_holidayperiods_id` = '$key_period_id' WHERE `glpi_plugin_activity_holidays`.`id` = ".$data['id'].";";
                              $DB->query($query_update);
                              break;
                           }
                        }
                     }
                  }
               }
            }
         }
      }
   }

   return true;
}