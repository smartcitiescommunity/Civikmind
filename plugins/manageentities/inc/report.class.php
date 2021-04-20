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

class PluginManageentitiesReport extends CommonDBTM {
   static $rightname = 'plugin_manageentities';

   /**
    * Report on the movement of technicians
    *
    * @param type  $entities_id
    * @param type  $category_id
    * @param type  $date1
    * @param type  $date2
    *
    * @global type $DB
    * @global type $CFG_GLPI
    *
    */
   function showMovingReports($entities_id, $category_id, $date1, $date2) {
      global $DB, $CFG_GLPI;

      $config = PluginManageentitiesConfig::getInstance();

      $resultat = [];
      $dbu      = new DbUtils();

      foreach ($entities_id as $entity_id) {

         $query = "SELECT `glpi_tickets`.`id` as tickets_id, `glpi_plugin_manageentities_cridetails`.`number_moving`, `glpi_plugin_manageentities_contracts`.`duration_moving` "
                  . " FROM `glpi_documents` "
                  . " LEFT JOIN `glpi_entities` ON (`glpi_documents`.`entities_id` = `glpi_entities`.`id`)"
                  . " LEFT JOIN `glpi_tickets` ON (`glpi_documents`.`tickets_id` = `glpi_tickets`.`id`)"
                  . " LEFT JOIN `glpi_plugin_manageentities_cridetails` ON (`glpi_documents`.`id` = `glpi_plugin_manageentities_cridetails`.`documents_id`) "
                  . " LEFT JOIN `glpi_plugin_manageentities_contracts` ON (`glpi_plugin_manageentities_contracts`.`contracts_id` = `glpi_plugin_manageentities_cridetails`.`contracts_id`) "
                  . " WHERE `documentcategories_id` = '" . $config->fields["documentcategories_id"] . "' "
                  . " AND `glpi_plugin_manageentities_cridetails`.`date` >= '" . $date1 . "' "
                  . " AND `glpi_plugin_manageentities_cridetails`.`date` <= '" . $date2 . "' "
                  . " AND `glpi_tickets`.`is_deleted` = 0 "
                  . " AND `glpi_plugin_manageentities_contracts`.`moving_management` = 1 "

                  . " AND `glpi_tickets`.`entities_id` = " . $entity_id;
         $query .= $dbu->getEntitiesRestrictRequest(" AND", "glpi_documents", '', '', true);

         $query .= " GROUP BY `glpi_documents`.`tickets_id` ";
         $query .= "ORDER BY `glpi_plugin_manageentities_cridetails`.`date` ASC";

         $result = $DB->query($query);

         $total_depl  = 0;
         $tickets_ids = [];
         while ($data = $DB->fetchArray($result)) {
            //time moving
            $total_depl                       += ($data['duration_moving'] * $data['number_moving']) / HOUR_TIMESTAMP;
            $tickets_ids[$data['tickets_id']] = $data['tickets_id'];

         }
         $resultat[$entity_id]['total_depl'] = $total_depl;
         $actiontime                         = 0;
         $tickets_ids                        = array_unique($tickets_ids);
         foreach ($tickets_ids as $ticket) {
            $tickettask = new TicketTask();
            $tasks      = $tickettask->find(['tickets_id' => $ticket, 'taskcategories_id' => $category_id]);

            foreach ($tasks as $task) {
               $actiontime += $task['actiontime'] / 2;
            }

         }
         $resultat[$entity_id]['actiontime'] = $actiontime / HOUR_TIMESTAMP;
         $resultat[$entity_id]['total']      = $total_depl - ($actiontime / HOUR_TIMESTAMP);
      }

      if (!empty($resultat)) {

         echo "<form method='post' action=\"./front/entity.php\">";
         echo "<div align='center'><table class='tab_cadre center' width='95%'>";
         echo "<tr><th colspan='4'>" . __('Report on the movement of technicians', 'manageentities') . "</th></tr>";
         echo "<tr>";

         echo "<th>" . __('Entity') . "</th>";
         echo "<th>" . __('Total moving package', 'manageentities') . "</th>";
         echo "<th>" . __('Total time of the tasks of a category', 'manageentities') . "</th>";
         echo "<th>" . __('Total') . "</th>";
         echo "</tr>";


         $i = 0;
         foreach ($resultat as $key => $row) {
            $i++;
            $class = " class='tab_bg_2 ";
            if ($i % 2) {
               $class = " class='tab_bg_1 ";
            }
            echo "<tr $class" . ($data["is_deleted"] == '1' ? "_2" : "") . "'>";
            echo "<td class='center'>" . Dropdown::getDropdownName("glpi_entities", $key) . "</td>";
            echo "<td class='center'>" . Html::formatNumber($row['total_depl']) . "</td>";
            echo "<td class='center'>" . Html::formatNumber($row['actiontime']) . "</td>";
            echo "<td class='center'>" . Html::formatNumber($row['total']) . "</td>";
            echo "</tr>";
         }
         echo "</table></div>";
         Html::closeForm();
      }
   }

   /**
    * Report concerning the occupation of the technicians
    *
    * @param type  $techs
    * @param type  $date1
    * @param type  $date2
    *
    * @global type $CFG_GLPI
    *
    * @global type $DB
    */
   function showOccupationReports($techs, $date1, $date2) {
      global $DB, $CFG_GLPI;

      $days = self::getDatesBetween2Dates($date1, $date2);
      $dbu  = new DbUtils();

      $resultat = [];
      foreach ($days as $key => $day) {
         $total_actiontime = 0;

         $hour = 0;
         switch ($day['day']) {
            case "Mon" :
            case "Tue" :
            case "Wed" :
            case "Thu":
               $hour = 8;
               break;
            case "Fri" :
               $hour = 7;
               break;
            case "Sat" :
            case "Sun" :
               $hour = 0;
               break;
         }

         if ($hour != 0) {
            $resultat[$day['date']] = [];
            foreach ($techs as $tech) {
               $actiontime = 0;
               $tickettask = new TicketTask();
               $date_begin = date('Y-m-d H:i:s', (strtotime($day['date'])));
               $date_end   = date('Y-m-d H:i:s', (strtotime($day['date'] . '+ 24 hours')));

               $query  = "SELECT `glpi_tickettasks`.*,
                       `glpi_tickets`.`id`AS tickets_id "
                         . " FROM `glpi_tickettasks`"
                         . " LEFT JOIN `glpi_tickets` ON (`glpi_tickettasks`.`tickets_id` = `glpi_tickets`.`id`)"
                         . " WHERE (`glpi_tickettasks`.`begin` >= '" . $date_begin . "' 
                  AND `glpi_tickettasks`.`end` <= '" . $date_end . "') "
                         . " AND `glpi_tickets`.`is_deleted` = 0"
                         . " AND `glpi_tickettasks`.`users_id_tech` = $tech "
                         . " AND `glpi_tickettasks`.`actiontime` != 0";
               $result = $DB->query($query);

               while ($task = $DB->fetchArray($result)) {
                  $actiontime       += $task['actiontime'];
                  $total_actiontime += $task['actiontime'];
               }

               $time                          = $actiontime / HOUR_TIMESTAMP;
               $resultat[$day['date']][$tech] = $time;
            }

            $resultat[$day['date']]['total']           = $total_actiontime / HOUR_TIMESTAMP;
            $resultat[$day['date']]['total_justified'] = (($total_actiontime / HOUR_TIMESTAMP) / (count($techs) * $hour)) * 100;

         }
      }

      if (!empty($resultat)) {

         echo "<form method='post' action=\"./front/entity.php\">";
         echo "<div align='center'><table class='tab_cadre center' width='95%'>";
         echo "<tr><th colspan='" . (count($techs) + 3) . "'>" . __('Report concerning the occupation of the technicians', 'manageentities') . "</th></tr>";
         echo "<tr>";
         echo "<th>" . __('Daily schedule', 'manageentities') . "</th>";
         echo "<th colspan='" . (count($techs) + 1) . "'>" . __('Technicians', 'manageentities') . "</th>";
         echo "<th>" . __('Time justified', 'manageentities') . "</th>";
         echo "</tr>";

         echo "<tr>";
         echo "<th>" . __('Date') . "</th>";
         foreach ($techs as $tech) {
            echo "<th>" . $dbu->getUserName($tech) . "</th>";
         }
         echo "<th>" . __('Total') . "</th>";
         echo "<th>" . __('% of time justified', 'manageentities') . "</th>";
         echo "</tr>";


         $i = 0;
         foreach ($resultat as $key => $data) {

            $i++;
            $class = " class='tab_bg_2 ";
            if ($i % 2) {
               $class = " class='tab_bg_1 ";
            }
            echo "<tr $class center'>";
            echo "<td class='center'>" . Html::convdate($key) . "</td>";
            foreach ($techs as $tech) {
               echo "<td class='center'>" . Html::formatNumber($data[$tech]) . "</td>";
            }
            echo "<td class='center'>" . Html::formatNumber($data['total']) . "</td>";
            echo "<td class='center'>" . Html::formatNumber($data['total_justified']) . "</td>";
            echo "</tr>";
         }
         echo "</table></div>";
         Html::closeForm();
      }
   }

   /**
    * List of dates between two dates
    *
    * @param type $startTime
    * @param type $endTime
    *
    * @return array
    */
   function getDatesBetween2Dates($startTime, $endTime) {

      $day       = 86400;
      $startTime = strtotime($startTime);
      $endTime   = strtotime($endTime);
      $numDays   = round(($endTime - $startTime) / $day) + 1;
      $days      = [];


      for ($i = 0; $i < $numDays; $i++) {
         $days[date('ymd', ($startTime + ($i * $day)))]['date'] = date('Y-m-d', ($startTime + ($i * $day)));
         $days[date('ymd', ($startTime + ($i * $day)))]['day']  = date('D', ($startTime + ($i * $day)));
      }

      return $days;
   }

   /**
    * function getDatesBetween
    * renvoie un tableau contenant toutes les dates, jour par jour,
    * comprises entre les deux dates passées en paramètre.
    *
    * @param (string) $dStart : date de départ
    * @param (string) $dEnd : date de fin
    *
    * @return (array) aDates : tableau des dates si succès
    * @return (bool) false : si échec
    */
   function getDatesBetween($dStart, $dEnd) {
      $iStart = strtotime($dStart);
      $iEnd   = strtotime($dEnd);
      if (false === $iStart || false === $iEnd) {
         return false;
      }
      $aStart = explode('-', $dStart);
      $aEnd   = explode('-', $dEnd);
      if (count($aStart) !== 3 || count($aEnd) !== 3) {
         return false;
      }
      if (false === checkdate($aStart[1], $aStart[2], $aStart[0]) || false === checkdate($aEnd[1], $aEnd[2], $aEnd[0]) || $iEnd <= $iStart) {
         return false;
      }
      for ($i = $iStart; $i < $iEnd + 86400; $i = strtotime('+1 day', $i)) {
         $sDateToArr                = strftime('%Y-%m-%d', $i);
         $sYear                     = substr($sDateToArr, 0, 4);
         $sMonth                    = substr($sDateToArr, 5, 2);
         $aDates[$sYear][$sMonth][] = $sDateToArr;
      }
      if (isset ($aDates) && !empty ($aDates)) {
         return $aDates;
      } else {
         return false;
      }
   }

}