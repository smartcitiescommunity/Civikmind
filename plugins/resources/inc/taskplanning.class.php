<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 resources plugin for GLPI
 Copyright (C) 2009-2016 by the resources Development Team.

 https://github.com/InfotelGLPI/resources
 -------------------------------------------------------------------------

 LICENSE

 This file is part of resources.

 resources is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 resources is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with resources. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */
if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginResourcesTaskPlanning
 */
class PluginResourcesTaskPlanning extends CommonDBTM {

   static $rightname = 'plugin_resources_task';

   /**
    * @return bool|\booleen
    */
   static function canCreate() {
      return (Session::haveRight(self::$rightname, UPDATE));
   }

   /**
    * @param int $nb
    *
    * @return string
    */
   static function getTypeName($nb = 0) {
      return sprintf(__('%1$s - %2$s'), _n('Human resource', 'Human resources', $nb, 'resources'), __('Tasks list', 'resources'));
   }

   /**
    * @param array $input
    *
    * @return array|bool
    */
   function prepareInputForAdd($input) {

      if (!isset($input["begin"]) || !isset($input["end"])) {
         return false;
      }

      $this->fields["begin"] = $input["begin"];
      $this->fields["end"]   = $input["end"];

      if (!$this->test_valid_date()) {
         self::displayError("date");
         return false;
      }
      $fup = new PluginResourcesTask();
      $fup->getFromDB($input["plugin_resources_tasks_id"]);

      if (isset($fup->fields["users_id"])) {
         Planning::checkAlreadyPlanned($fup->fields["users_id"], $input["begin"], $input["end"]);
      }
      return $input;
   }

   function post_addItem() {
      global $CFG_GLPI;

      // Auto update actiontime
      $fup = new PluginResourcesTask();
      $fup->getFromDB($this->input["plugin_resources_tasks_id"]);
      if ($fup->fields["actiontime"] == 0) {
         $timestart                 = strtotime($this->input["begin"]);
         $timeend                   = strtotime($this->input["end"]);
         $updates2[]                = "actiontime";
         $fup->fields["actiontime"] = $timeend - $timestart;
         $fup->updateInDB($updates2);
      }
   }

   /**
    * @param array $input
    *
    * @return array|bool
    */
   function prepareInputForUpdate($input) {
      global $CFG_GLPI;

      $this->getFromDB($input["id"]);
      // Save fields
      $oldfields             = $this->fields;
      $this->fields["begin"] = $input["begin"];
      $this->fields["end"]   = $input["end"];

      $fup = new PluginResourcesTask();
      $fup->getFromDB($input["plugin_resources_tasks_id"]);

      if (!$this->test_valid_date()) {
         $this->displayError("date");
         return false;
      }
      if (isset($fup->fields["users_id"])) {
         Planning::checkAlreadyPlanned($fup->fields["users_id"], $input["begin"], $input["end"], ['PluginResourcesTask' => [$input["id"]]]);
      }
      // Restore fields
      $this->fields = $oldfields;

      return $input;
   }

   /**
    * @param int $history
    */
   function post_updateItem($history = 1) {
      global $CFG_GLPI;

      $fup = new PluginResourcesTask();
      $fup->getFromDB($this->input["plugin_resources_tasks_id"]);
      $timestart                 = strtotime($this->input["begin"]);
      $timeend                   = strtotime($this->input["end"]);
      $updates2[]                = "actiontime";
      $fup->fields["actiontime"] = $timeend - $timestart;
      $fup->updateInDB($updates2);
   }

   /**
    * Read the planning information associated with a task
    *
    * @param $plugin_resources_tasks_id integer ID of the task
    *
    * @return bool, true if exists
    */
   function getFromDBbyTask($plugin_resources_tasks_id) {
      global $DB;

      $query = "SELECT *
                FROM `" . $this->getTable() . "`
                WHERE `plugin_resources_tasks_id` = '$plugin_resources_tasks_id'";

      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetchAssoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         }
      }
      return false;
   }

   /**
    * @param                      $resources
    * @param \PluginResourcesTask $task
    */
   function showFormForTask($resources, PluginResourcesTask $task) {
      global $CFG_GLPI;

      $PluginResourcesResource = new PluginResourcesResource();
      $PluginResourcesResource->getFromDB($resources);
      $taskid = $task->getField('id');
      if ($taskid > 0 && $this->getFromDBbyTask($taskid)) {
         if ($this->canCreate()) {
            echo "<script type='text/javascript' >\n";
            echo "function showPlan" . $taskid . "(){\n";
            echo "$('#plan').css({'display':'none'});";
            $params = [
               'form'   => 'followups',
               'id'     => $this->fields["id"],
               'begin'  => $this->fields["begin"],
               'end'    => $this->fields["end"],
               'entity' => $PluginResourcesResource->fields["entities_id"]
            ];
            Ajax::updateItemJsCode('viewplan', $CFG_GLPI["root_doc"] . "/plugins/resources/ajax/planning.php", $params);
            echo "}";
            echo "</script>\n";
            echo "<div id='plan' onClick='showPlan" . $taskid . "()'>\n";
            echo "<span class='showplan'>";
         }
         if ($this->fields["begin"] && $this->fields["end"]) {
            echo Html::convDateTime($this->fields["begin"]) .
                 "&nbsp;->&nbsp;" . Html::convDateTime($this->fields["end"]);
         } else {
            echo __('Plan this task');
         }
         if ($this->canCreate()) {
            echo "</span>";
            echo "</div>\n";
            echo "<div id='viewplan'></div>\n";
         }
      } else {
         if ($this->canCreate()) {
            echo "<script type='text/javascript' >\n";
            echo "function showPlanUpdate(){\n";
            echo "$('#plan').css({'display':'none'});";
            $params = ['form'   => 'followups',
                       'entity' => $_SESSION["glpiactive_entity"]];
            Ajax::updateItemJsCode('viewplan', $CFG_GLPI["root_doc"] . "/plugins/resources/ajax/planning.php", $params);
            echo "};";
            echo "</script>";

            echo "<div id='plan'  onClick='showPlanUpdate()'>\n";
            echo "<span class='showplan'>" . __('Plan this task') . "</span>";
            echo "</div>\n";
            echo "<div id='viewplan'></div>\n";
         } else {
            echo __('None');
         }
      }
   }

   // SPECIFIC FUNCTIONS

   /**
    * Current dates are valid ? begin before end
    *
    * @return boolean
    * */
   function test_valid_date() {
      return (!empty($this->fields["begin"]) && !empty($this->fields["end"]) && strtotime($this->fields["begin"]) < strtotime($this->fields["end"]));
   }

   /**
    * Add error message to message after redirect
    *
    * @param $type error type : date / is_res / other
    *
    * @return nothing
    * */
   static function displayError($type) {

      switch ($type) {
         case "date" :
            Session::addMessageAfterRedirect(
               __('Error in entering dates. The starting date is later than the ending date'), false, ERROR);
            break;

         default :
            Session::addMessageAfterRedirect(__('Unknown error'), false, ERROR);
            break;
      }
   }

   /**
    * Display a Planning Item
    *
    * @param $val Array of the item to display
    *
    * @return Already planned information
    * */
   static function getAlreadyPlannedInformation($val) {
      global $CFG_GLPI;

      $out = "";

      $out .= PluginResourcesResource::getTypeName() . " - " . PluginResourcesTask::getTypeName() . ' : ' . Html::convDateTime($val["begin"]) . ' -> ' .
              Html::convDateTime($val["end"]) . ' : ';
      $out .= "<a href='" . $CFG_GLPI["root_doc"] . "/plugins/resources/front/task.form.php?id=" .
              $val["plugin_resources_tasks_id"] . "'>";
      $out .= Html::resume_text($val["name"], 80) . '</a>';

      return $out;
   }

   /**
    * Populate the planning with plannedresource tasks
    *
    * @param $who ID of the user (0 = undefined)
    * @param $who_group ID of the group of users (0 = undefined, mine = login user ones)
    * @param $begin Date
    * @param $end Date
    *
    * @return array of planning item
    */
   static function populatePlanning($parm) {
      global $DB, $CFG_GLPI;

      $output = [];

      if (!isset($parm['begin']) || $parm['begin'] == 'NULL' || !isset($parm['end']) || $parm['end'] == 'NULL') {
         return $parm;
      }

      $who       = $parm['who'];
      $who_group = $parm['whogroup'];
      $begin     = $parm['begin'];
      $end       = $parm['end'];
      // Get items to print
      $ASSIGN = "";

//      if ($who_group === "mine") {
//         if (count($_SESSION["glpigroups"])) {
//            $groups = implode("','", $_SESSION['glpigroups']);
//            $ASSIGN = " `glpi_plugin_resources_tasks`.`users_id` IN (SELECT DISTINCT `users_id`
//                                    FROM `glpi_groups_users`
//                                    WHERE `groups_id` IN ('$groups'))
//                                          AND ";
//         } else { // Only personal ones
//            $ASSIGN = "`glpi_plugin_resources_tasks`.`users_id` = '$who'
//                     AND ";
//         }
//      } else {
         if ($who > 0) {
            $ASSIGN = "`glpi_plugin_resources_tasks`.`users_id` = '$who'
                     AND ";
         }
         if ($who_group > 0) {
            $ASSIGN = "`glpi_plugin_resources_tasks`.`users_id` IN (SELECT `users_id`
                                    FROM `glpi_groups_users`
                                    WHERE `groups_id` = '$who_group')
                                          AND ";
         }
//      }
      if (empty($ASSIGN)) {
         $ASSIGN = "`glpi_plugin_resources_tasks`.`users_id` IN (SELECT DISTINCT `glpi_profiles_users`.`users_id`
                                 FROM `glpi_profiles`
                                 LEFT JOIN `glpi_profiles_users`
                                    ON (`glpi_profiles`.`id` = `glpi_profiles_users`.`profiles_id`)
                                 WHERE `glpi_profiles`.`interface`='central' ";
         $dbu = new DbUtils();
         $ASSIGN .= $dbu->getEntitiesRestrictRequest("AND", "glpi_profiles_users", '', $_SESSION["glpiactive_entity"], 1);
         $ASSIGN .= ") AND ";
      }

      $query = "SELECT `glpi_plugin_resources_tasks`.*,
                        `glpi_plugin_resources_taskplannings`.`begin`, 
                        `glpi_plugin_resources_taskplannings`.`end`,
                        `glpi_plugin_resources_resources`.`name` as resource,
                        `glpi_plugin_resources_tasktypes`.`name` as type
                FROM `glpi_plugin_resources_tasks`
                LEFT JOIN `glpi_plugin_resources_taskplannings` ON (`glpi_plugin_resources_taskplannings`.`plugin_resources_tasks_id` = `glpi_plugin_resources_tasks`.`id`)
                LEFT JOIN `glpi_plugin_resources_resources` 
                ON (`glpi_plugin_resources_resources`.`id` = `glpi_plugin_resources_tasks`.`plugin_resources_resources_id`)
                LEFT JOIN `glpi_plugin_resources_tasktypes` 
                ON (`glpi_plugin_resources_tasktypes`.`id` = `glpi_plugin_resources_tasks`.`plugin_resources_tasktypes_id`)
                WHERE $ASSIGN
                      '$begin' < `end` AND '$end' > `begin` AND `glpi_plugin_resources_tasks`.`is_finished` != 1
                ORDER BY `begin`";

      $result = $DB->query($query);

      if ($DB->numrows($result) > 0) {
         for ($i = 0; $data = $DB->fetchArray($result); $i++) {

            $key                                           = $parm["begin"] . $data["id"] . "$$$" . "plugin_resource";
            $output[$key]['color']                         = $parm['color'];
            $output[$key]['event_type_color']              = $parm['event_type_color'];
            $output[$key]["id"]                            = $data["id"];
            $output[$key]["plugin_resources_resources_id"] = $data["plugin_resources_resources_id"];
            $output[$key]["users_id"]                      = $data["users_id"];
            $output[$key]["begin"]                         = $data["begin"];
            $output[$key]["end"]                           = $data["end"];
            $output[$key]["name"]                          = $data["name"];
            $output[$key]["type"]                          = $data["type"];
            $output[$key]["resource"]                      = $data["resource"];
            $output[$key]["content"]                       = Html::resume_text($data["comment"], $CFG_GLPI["cut"]);
            $output[$key]["itemtype"]                      = 'PluginResourcesTaskPlanning';
            $output[$key]["url"]                           = $CFG_GLPI["root_doc"] . "/plugins/resources/front/task.form.php?id=" . $data['id'];;
         }
      }
      return $output;
   }

   /**
    * Display a Planning Item
    *
    * @param $parm Array of the item to display
    *
    * @return Nothing (display function)
    * */
   static function displayPlanningItem(array $val, $who, $type = "", $complete = 0) {
      global $CFG_GLPI;

      $html = "";

      $rand = mt_rand();
      $html .= "<a href='" . $CFG_GLPI["root_doc"] . "/plugins/resources/front/task.form.php?id=" . $val["id"] . "'";

      $html .= " onmouseout=\"cleanhide('content_task_" . $val["id"] . $rand . "')\"
               onmouseover=\"cleandisplay('content_task_" . $val["id"] . $rand . "')\"";
      $html .= ">";

      switch ($type) {
         case "in" :
            //TRANS: %1$s is the start time of a planned item, %2$s is the end
            $beginend = sprintf(__('From %1$s to %2$s'), date("H:i", strtotime($val["begin"])), date("H:i", strtotime($val["end"])));
            $html     .= sprintf(__('%1$s %2$s'), $beginend, Html::resume_text($val["name"], 80));

            break;
         case "begin" :
            $start = sprintf(__('Start at %s'), date("H:i", strtotime($val["begin"])));
            $html  .= sprintf(__('%1$s: %2$s'), $start, Html::resume_text($val["name"], 80));
            break;

         case "end" :
            $end  = sprintf(__('End at %s'), date("H:i", strtotime($val["end"])));
            $html .= sprintf(__('%1$s: %2$s'), $end, Html::resume_text($val["name"], 80));
            break;
      }

      if ($val["users_id"] && $who == 0) {
         $dbu = new DbUtils();
         $html .= " - " . __('User') . " " . $dbu->getUserName($val["users_id"]);
      }
      $html .= "</a><br>";

      $html .= PluginResourcesResource::getTypeName(1) .
               " : <a href='" . $CFG_GLPI["root_doc"] . "/plugins/resources/front/resource.form.php?id=" .
               $val["plugin_resources_resources_id"] . "'";
      $html .= ">" . $val["resource"] . "</a>";

      $html .= "<div class='over_link' id='content_task_" . $val["id"] . $rand . "'>";
      if ($val["end"]) {
         $html .= "<strong>" . __('End date') . "</strong> : " . Html::convdatetime($val["end"]) . "<br>";
      }
      if ($val["type"]) {
         $html .= "<strong>" . PluginResourcesTaskType::getTypeName(1) . "</strong> : " .
                  $val["type"] . "<br>";
      }
      if ($val["content"]) {
         $html .= "<strong>" . __('Description') . "</strong> : " . $val["content"];
      }
      $html .= "</div>";

      return $html;
   }

}

