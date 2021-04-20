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


class PluginActivityStats5 implements PluginActivityInterface {

   private $months = [];

   public function getData(&$params) {
      global $DB;

      $year = $params['year'];

      $months = Toolbox::getMonthsOfYearArray();
      $current_month = date("n");

      $query_opened_tickets = "SELECT COUNT(*) as count 
                     FROM `glpi_tickets` 
                     LEFT JOIN `glpi_groups_tickets` 
                  ON (`glpi_tickets`.`id` = `glpi_groups_tickets`.`tickets_id` 
                  AND `glpi_groups_tickets`.`type` = '".Ticket::ASSIGNED."')
                     WHERE `glpi_tickets`.`is_deleted` = 0 ";

      if ($params['groups_id'] > 0) {
         $query_opened_tickets.= "AND `glpi_groups_tickets`.`groups_id` = ".$params['groups_id']." ";
      }

      $query_group_member = "SELECT GROUP_CONCAT(`glpi_users`.`id`) AS listuser "
                        ."FROM `glpi_users` "
                        ."LEFT JOIN `glpi_groups_users` ON (`glpi_users`.`id` = `glpi_groups_users`.`users_id`) "
                        ."WHERE `glpi_groups_users`.`groups_id` = ".$params['groups_id']." "
                        ." GROUP BY `glpi_groups_users`.`groups_id`";

      $result_gu = $DB->fetchArray($DB->query($query_group_member));
      $techlist = explode(',', $result_gu['listuser']);

      $nb_ticket = [];
      $nb_tech = [];
      $entitiesRestrict['glpi_tickets'] = PluginActivityTools::getSpecificEntityRestrict('glpi_tickets', $params);
      $entitiesRestrict['glpi_plugin_mydashboard_stocktickets'] = PluginActivityTools::getSpecificEntityRestrict('glpi_plugin_mydashboard_stocktickets', $params);
      $entitiesRestrict['glpi_profiles_users'] = PluginActivityTools::getSpecificEntityRestrict('glpi_profiles_users', $params);
      $entitiesRestrict['glpi_plugin_activity_activities'] = PluginActivityTools::getSpecificEntityRestrict('glpi_plugin_activity_activities', $params);

      $this->months = PluginActivityTools::getMonths($params);
      $params['maxyaxis'] = 0;
      foreach ($this->months as $key => $month) {
         //Not ticks
         if (!is_numeric($key)) {
            continue;
         }

         if ($month['month'] > $current_month && $month['year'] == date("Y")) {
            break;
         }

         $next = $month['month']+1;

         $month_tmp = $month['month'];
         $nb_jours = date("t", mktime(0, 0, 0, $month['month'], 1, $month['year']));

         if (strlen($key) == 1) {
            $month_tmp = "0".$month_tmp;
         }
         if (strlen($next) == 1) {
            $next = "0".$next;
         }

         if ($key == 0) {
            $year = $year-1;
            $month_tmp = "12";
            $nb_jours = date("t", mktime(0, 0, 0, 12, 1, $month['year']));
         }

         $month_deb_date = $month['year']."-$month_tmp-01";
         $month_deb_datetime = $month_deb_date." 00:00:00";
         $month_end_date = $month['year']."-$month_tmp-$nb_jours";
         $month_end_datetime = $month_end_date." 23:59:59";

         /*if(isset($params['nb_ticket']) && $params['nb_ticket'] == 1){
            $querym_t = $query_opened_tickets."AND ("
                    . "((`glpi_tickets`.`date` <= '$month_end_datetime') AND `status` NOT IN (".Ticket::SOLVED.",".Ticket::CLOSED.")) "
                    . "OR ((`glpi_tickets`.`date` <= '$month_end_datetime') AND (`glpi_tickets`.`closedate` > ADDDATE('$month_end_date 00:00:00' , INTERVAL 1 DAY)))"
                    . ")"
                    .$entitiesRestrict['glpi_tickets'] ;
            $result_t = $DB->fetchArray($DB->query($querym_t));
            if(isset($result_t['count'])) {
               $nb_ticket[] = array($key,$result_t['count']);
               if($result_t['count'] > $params['maxyaxis']){
                  $params['maxyaxis'] =  $result_t['count'];
               }
            }
         }*/

         if (isset($params['nb_ticket']) && $params['nb_ticket'] == 1) {
            $querym_t = "SELECT SUM(`nbstocktickets`) as count FROM glpi_plugin_mydashboard_stocktickets "
                      . "WHERE date = '$month_end_date' ".$entitiesRestrict['glpi_plugin_mydashboard_stocktickets'];
            $result_t = $DB->fetchArray($DB->query($querym_t));
            if (isset($result_t['count'])) {
               $nb_ticket[] = [$key, $result_t['count']];
               if ($result_t['count'] > $params['maxyaxis']) {
                  $params['maxyaxis'] = $result_t['count'];
               }
            }

            if ($key == date("m") && $year == date("Y")) {
               $querym_t2 = "SELECT COUNT(*) as count FROM `glpi_tickets`
                             WHERE `glpi_tickets`.`is_deleted` = 0 " . $entitiesRestrict['glpi_tickets'] . "
                             AND (((`glpi_tickets`.`date` <= '$month_end_date 23:59:59') 
                             AND `status` NOT IN (" . CommonITILObject::SOLVED . "," . CommonITILObject::CLOSED . ")) 
                             OR ((`glpi_tickets`.`date` <= '$month_end_date 23:59:59')
                             AND (`glpi_tickets`.`solvedate` > ADDDATE('$month_end_date 00:00:00' , INTERVAL 1 DAY))))";

               $result_t2 = $DB->fetchArray($DB->query($querym_t2));
               if (isset($result_t2['count'])) {
                  $nb_ticket[] = [$key, $result_t2['count']];

                  if ($result_t2['count'] > $params['maxyaxis']) {
                     $params['maxyaxis'] = $result_t2['count'];
                  }
               }
            }
         }

         if (isset($params['nb_tech']) && $params['nb_tech'] == 1) {
               $holiday = new PluginActivityHoliday();
               $nb_worked_days = $nb_jours - $holiday->countWe($month_deb_date, $month_end_date);
               $tot_time[$key] = 0;
            foreach ($techlist as $techid) {

               //Gestion plateforme
               $querym_at = "SELECT `glpi_plugin_activity_activities`.`plugin_activity_activitytypes_id` AS type, `glpi_plugin_activity_activities`.`actiontime` AS actiontime, `glpi_plugin_activity_activitytypes`.`completename` AS name 
                              FROM `glpi_plugin_activity_activities` 
                              INNER JOIN `glpi_plugin_activity_activitytypes` ON (`glpi_plugin_activity_activitytypes`.`id` = `glpi_plugin_activity_activities`.`plugin_activity_activitytypes_id`)
                              WHERE (`glpi_plugin_activity_activities`.`begin` >= '$month_deb_datetime' AND `glpi_plugin_activity_activities`.`begin` <= '$month_end_datetime')";

               if (count($techlist) > 1) {
                  $querym_at .= "AND `glpi_plugin_activity_activities`.`users_id` = ".$techid." ";
               }

               $querym_at .=$entitiesRestrict['glpi_plugin_activity_activities']
                           ."AND `glpi_plugin_activity_activities`.`plugin_activity_activitytypes_id` IN (11) 
                              ORDER BY name";

               $result_at_q = $DB->query($querym_at);

               while ($data = $DB->fetchAssoc($result_at_q)) {
                  $tot_time[$key] += (PluginActivityReport::TotalTpsPassesArrondis($data['actiontime']/3600/8))/$nb_worked_days;
               }
               //Assistance interne TODO Voir le OR tache non planifi�es
               $querym_ai = "SELECT  DATE(`glpi_tickettasks`.`date`), SUM(`glpi_tickettasks`.`actiontime`) AS actiontime_date
                              FROM `glpi_tickettasks` 
                              INNER JOIN `glpi_tickets` ON (`glpi_tickets`.`id` = `glpi_tickettasks`.`tickets_id` AND `glpi_tickets`.`is_deleted` = 0) "
               //                              LEFT JOIN `glpi_entities` ON (`glpi_tickets`.`entities_id` = `glpi_entities`.`id`)
                           ."INNER JOIN `glpi_plugin_activity_tickettasks` ON (`glpi_tickettasks`.`id` = `glpi_plugin_activity_tickettasks`.`tickettasks_id`) 
                              WHERE (
                                 `glpi_tickettasks`.`begin` >= '$month_deb_datetime' 
                                 AND `glpi_tickettasks`.`end` <= '$month_end_datetime' ";
               if (count($techlist) > 1) {
                  $querym_ai .= " AND `glpi_tickettasks`.`users_id_tech` = (".$techid.") ";
               }

               $querym_ai .= $entitiesRestrict['glpi_tickets']
                           .") 
                              OR (
                                 `glpi_tickettasks`.`date` >= '$month_deb_datetime' 
                                 AND `glpi_tickettasks`.`date` <= '$month_end_datetime' ";

               if (count($techlist) > 1) {
                  $querym_ai .= "AND `glpi_tickettasks`.`users_id` = (".$techid.") ";
               }

               $querym_ai .= "AND `glpi_tickettasks`.`begin` IS NULL "
                              .$entitiesRestrict['glpi_tickets']
                           .")
                                 AND `glpi_tickettasks`.`actiontime` != 0 AND `glpi_plugin_activity_tickettasks`.`is_oncra` = 1 
                              GROUP BY DATE(`glpi_tickettasks`.`date`);
                              ";
               $result_ai_q = $DB->query($querym_ai);
               while ($data = $DB->fetchAssoc($result_ai_q)) {
                  $tot_time/*[$techid]*/[$key] += (PluginActivityReport::TotalTpsPassesArrondis($data['actiontime_date']/3600/8))/$nb_worked_days;
               }
            }
            //      }
         }

         if ($key == 0) {
            $year++;
         }
      }

      if (isset($params['nb_tech']) && $params['nb_tech'] == 1) {
         $tot_time_data = [];
         foreach ($tot_time as $key => $time) {
            $tot_time_data[] = [$key,$time];
         }

         $data[] = [
              "data" => $tot_time_data,
              "label" => __("Number of technicians", "activity"),
              "bars" => [
                  "show" => true,
                  "barWidth"=> 0.8,
                  "lineWidth" => 0,
                  "shadowSize" => 0,
                  "fillOpacity" => 0.9
              ],
              "markers" => ["show" => true,"position" => "ct"],
              "yaxis" => 2,

          ];
         $params['colors'][] = '#CC0000';
      }

      if (isset($params['nb_ticket']) && $params['nb_ticket'] == 1) {
         $data[] = [
              "data" => $nb_ticket,
              "label" => __("Number of tickets in progress", "activity"),
              "lines" => [
                  "show" => true,
              ],
              "markers" => ["show" => true,"position" => "ct"]
          ];
         $params['colors'][] = '#2ca02c';
      }

      return $data;
   }

   public function initParams($post) {
      $params = [
         'year' => date("Y"),
         'entities_id' => $_SESSION["glpiactive_entity"],
         'groups_id' => 0,
         'nb_ticket' => 1,
         'nb_tech' => 1,
         'all_period' => 0
      ];

      PluginActivityTools::initParams($params, $post);
      return $params;
   }

   public function showForm($params) {

      $params['labels'] = [
         'nb_ticket' => __("Number of tickets in progress", "activity"),
         'nb_tech' => __("Number of technicians", "activity"),
         'all_period' => __("All the period", "activity")
      ];

      return PluginActivityTools::showForm($params);
   }

   public function showLine($params, $data) {
      $params['data'] = $data;
      $params['title'] = $this->getTitle();
      $params['months'] = $this->months;
      $params['maxy2axis'] = 5;
      PluginActivityTools::showLine($params, $this);
   }

   public static function getTitle() {
      return __("Main evolution of team", 'activity');
   }

}
