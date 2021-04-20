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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginActivityProjectTask extends CommonDBTM {

   var $dohistory = false;

   static $rightname = "plugin_activity";

   /**
    * functions mandatory
    * getTypeName(), canCreate(), canView()
    * */
   static function getTypeName($nb = 0) {
      return Project::getTypeName($nb);
   }

   static function taskUpdate(ProjectTask $item) {

      if (!is_array($item->input) || !count($item->input)) {
         // Already cancel by another plugin
         return false;
      }

      self::setProjectTask($item);

//      if (isset($item->input['plan'])) {
//         self::manageBeginAndEndPlanDates($item->input);
//      }
   }

   static function taskAdd(ProjectTask $item) {

      if (!is_array($item->input) || !count($item->input)) {
         // Already cancel by another plugin
         return false;
      }

      self::setProjectTask($item);
   }

   static function setProjectTask(ProjectTask $item) {
      if (self::canCreate()) {
         $projecttask = new self();
         if (isset($item->input['id'])
             && isset($item->input['is_oncra'])) {
            $projecttask->getFromDBForTask($item->input['id']);

            if (!empty($projecttask->fields)) {
               $projecttask->update(['id'             => $projecttask->fields['id'],
                                    'is_oncra'       => $item->input['is_oncra'],
                                    'projecttasks_id' => $item->input['id']]);
            } else {
               $projecttask->add(['is_oncra'        => $item->input['is_oncra'],
                                 'projecttasks_id' => $item->getID()]);
            }
         } else {
            $is_cra_default = 0;
            $opt = new PluginActivityOption();
            $opt->getFromDB(1);
            if ($opt) {
               $is_cra_default = $opt->fields['is_cra_default'];
            }
            $projecttask->add(['is_oncra'        => isset($item->input['is_oncra']) ? $item->input['is_oncra'] : $is_cra_default,
                              'projecttasks_id' => $item->getID()]);
         }
      }
   }


   function getFromDBForTask($projecttasks_id) {
      $dbu = new DbUtils();
      $data = $dbu->getAllDataFromTable($this->getTable(), [$dbu->getForeignKeyFieldForTable('glpi_projecttasks') => $projecttasks_id]);

      $this->fields = array_shift($data);
   }

   static function addField($params) {

      $item = $params['item'];

      if ($item->getType() == 'ProjectTask') {
         $opt = new PluginActivityOption();
         $opt->getFromDB(1);

         $projecttask = new self();

         $is_cra_default = $opt->getIsCraDefaultProject();

         if ($item->getID()) {

            $projecttask->getFromDBForTask($item->getID());
            $is_cra_default = $projecttask->fields['is_oncra'];
         }


         echo '<td>';
         echo __('Use in CRA', 'activity');
         echo '</td>';
         echo '<td>';

         Dropdown::showYesNo('is_oncra', $is_cra_default, -1, ['value' => 1]);
         echo '</td>';
         echo '<td colspan="2"></td>';
      }

   }

   static function queryProjectTask($criteria) {

      $dbu = new DbUtils();
      $begin = $criteria["begin"];
      $end   = $criteria["end"];
      $who   = $criteria["users_id"];

      if ($who > 0) {
         $ASSIGN = "`glpi_projecttaskteams`.`itemtype` = 'User'
                       AND `glpi_projecttaskteams`.`items_id` = '$who'
                       AND ";
      }

      if (empty($ASSIGN)) {
         $ASSIGN = "`glpi_projecttaskteams`.`itemtype` = 'User'
                       AND `glpi_projecttaskteams`.`items_id`
                        IN (SELECT DISTINCT `glpi_profiles_users`.`users_id`
                            FROM `glpi_profiles`
                            LEFT JOIN `glpi_profiles_users`
                                 ON (`glpi_profiles`.`id` = `glpi_profiles_users`.`profiles_id`)
                            WHERE `glpi_profiles`.`interface` = 'central' ".
                   $dbu->getEntitiesRestrictRequest("AND", "glpi_profiles_users", '',
                                              $_SESSION["glpiactive_entity"], 1).")
                     AND ";
      }

      $DONE_EVENTS = '';
//      if (!isset($options['display_done_events']) || !$options['display_done_events']) {
         $DONE_EVENTS = "`glpi_projecttasks`.`percent_done` < 100
                         AND (glpi_projectstates.is_finished = 0
                              OR glpi_projectstates.is_finished IS NULL)
                         AND ";
//      }

      $query = "SELECT `glpi_projecttasks`.*
                FROM `glpi_projecttaskteams`
                INNER JOIN `glpi_projecttasks`
                  ON (`glpi_projecttasks`.`id` = `glpi_projecttaskteams`.`projecttasks_id`)
                LEFT JOIN `glpi_projectstates`
                  ON (`glpi_projecttasks`.`projectstates_id` = `glpi_projectstates`.`id`)
                LEFT JOIN `glpi_plugin_activity_projecttasks` 
                ON `glpi_plugin_activity_projecttasks`.`projecttasks_id` = `glpi_projecttasks`.`id`
                WHERE `glpi_plugin_activity_projecttasks`.`is_oncra` = 1
                      AND $ASSIGN
                      $DONE_EVENTS
                      '$begin' < `glpi_projecttasks`.`plan_end_date`
                      AND '$end' > `glpi_projecttasks`.`plan_start_date`
                ORDER BY `glpi_projecttasks`.`plan_start_date`";

      return $query;
   }

}