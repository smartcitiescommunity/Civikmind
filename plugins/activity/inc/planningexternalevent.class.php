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

class PluginActivityActions {
   const ADD_ACTIVITY     = 'add_activity';
   const LIST_ACTIVITIES  = 'list_activities';
   const HOLIDAY_REQUEST  = 'holiday_request';
   const LIST_HOLIDAYS    = 'list_holidays';
   const APPROVE_HOLIDAYS = 'validate_holidays';
   const CRA              = 'cra';
   const HOLIDAY_COUNT    = 'holiday_count';
   const MANAGER          = 'manager';
}

class PluginActivityPlanningExternalEvent extends CommonDBTM {

   // Event color
   static $HOLIDAY_COLOR        = "#7DAEDF";
   static $ACTIVITY_COLOR       = "#84BE6A";
   static $MANAGEENTITIES_COLOR = "#08A5AC";
   static $TICKET_COLOR         = "#E85F0C";

   // Calendar views
   static $DAY   = 'agendaDay';
   static $WEEK  = 'agendaWeek';
   static $MONTH = 'month';

   // Event tags
   static $TICKET_TAG         = 'ticket';
   static $MANAGEENTITIES_TAG = 'manageentities';
   static $ACTIVITY_TAG       = 'activity';
   static $HOLIDAY_TAG        = 'holiday';

   var $dohistory = false;

   static $rightname = "plugin_activity";


   /**
    * functions mandatory
    * getTypeName(), canCreate(), canView()
    * */
   static function getTypeName($nb = 0) {
      return PlanningExternalEvent::getTypeName($nb);
   }

   static function cleanForItem(CommonDBTM $item) {

      $temp = new self();
      $temp->deleteByCriteria(
         ['planningexternalevents_id' => $item->getField('id')]
      );
   }

   /**
    * Display menu
    */
   static function menu($class, $listActions, $widget = false) {
      global $CFG_GLPI;

      $i = 0;

      $allactions = [];
      foreach ($listActions as $actions) {
         if ($actions['rights']) {
            $allactions[] = $actions;
         }
      }

      $number = count($allactions);
      $return = "";
      if ($number > 0) {
         $return .= Ajax::createIframeModalWindow('holiday',
            $CFG_GLPI["root_doc"] . "/plugins/activity/front/holiday.form.php",
            ['title'         => __('Create a holiday request', 'activity'),
               'reloadonclose' => false,
               'width'         => 1180,
               'height'        => 500,
               'display'       => false,
            ]);

         $return .= "<div align='center'>";

         if (!$widget) {
            $return .= "<table class='tab_cadre' cellpadding='5'>";
         }

         if (!$widget) {
            $return .= "<tr><th colspan='4'>" . $class::getTypeName(2) . "</th></tr>";
         } else {
            $return .= "<div class=\"tickets-stats\">";
         }

         foreach ($allactions as $action) {
            if (!$widget) {
               if ((($i % 2) == 0) && ($number > 1)) {
                  $return .= "<tr class='twhite'>";
               }
               if ($number == 1) {
                  $return .= "<tr class='twhite'>";
               }
            }
            if (!$widget) {
               $return .= "<td class='center'>";
            } else {
               $return .= "<div class='nb'>";
            }
            $return .= "<a href=\"" . $action['link'] . "\"";
            if (isset($action['onclick']) && !empty($action['onclick'])) {
               $return .= "onclick=\"" . $action['onclick'] . "\"";
            }
            $return .= ">";
            $return .= "<i class='" . $action['img'] . " fa-5x' style='color:#b5b5b5' title='" . $action['label'] . "'></i>";
            $return .= "<br><br>" . $action['label'] . "</a>";

            if (!$widget) {
               $return .= "</td>";
            } else {
               $return .= "</div>";
            }
            $i++;
            if (!$widget) {
               if (($i == $number) && (($number % 2) != 0) && ($number > 1)) {
                  $return .= "<td></td>";
                  $return .= "</tr>";
               }
            }
         }
         if ($widget) {
            $return .= "</tr>";
         } else {
            $return .= "</div>";
         }

         if (!$widget) {
            $return .= "</table>";
         }
         $return .= "</div>";
      }

      return $return;
   }

   static function getActionsOn() {
      global $CFG_GLPI;

      // Array of action user can do :
      //    link     -> url of link
      //    img      -> ulr of the img to show
      //    label    -> label to show
      //    onclick  -> if set, set the onclick value of the href
      //    rights   -> if true, action shown

      $listActions = [
         PluginActivityActions::ADD_ACTIVITY    => [
            'link'   => $CFG_GLPI["root_doc"] . "/front/planning.php",//plugins/activity/front/activity.form.php
            'img'    => "far fa-calendar-plus",
            'label'  => __('Add an activity', 'activity'),
            'rights' => Session::haveRight("plugin_activity", CREATE),
         ],
         PluginActivityActions::LIST_ACTIVITIES => [
            'link'   => $CFG_GLPI["root_doc"] . "/plugins/activity/front/planningexternalevent.php",
            'img'    => "far fa-calendar-alt",
            'label'  => __('List of activities', 'activity'),
            'rights' => Session::haveRight("plugin_activity", READ),
         ],
         PluginActivityActions::CRA             => [
            'link'   => $CFG_GLPI["root_doc"] . "/plugins/activity/front/cra.php",
            'img'    => "far fa-calendar-check",
            'label'  => __('CRA', 'activity'),
            'rights' => Session::haveRight("plugin_activity_statistics", 1),
         ]
      ];

      return $listActions;
   }



   static public function addCra($params) {
      $item       = $params['item'];
      switch ($item->getType()) {
         case 'PlanningExternalEvent':
            $self = new self();
            if ($item->getID() && !empty($item->getID())) {
               $self->getFromDBForTask($item->getID());
            } else {
               $self->getEmpty();
            }

            $is_cra_default = 0;
            $opt            = new PluginActivityOption();
            $opt->getFromDB(1);
            if ($opt) {
               $is_cra_default = $opt->fields['is_cra_default'];
            }

            if (Session::haveRight("plugin_activity_statistics", 1)) {
               echo "<tr class='tab_bg_1'>";
               echo "<td colspan='1'></td>";
               echo '<td>';
               echo "<div id='is_oncra_" . $item->getID() . "' class='fa-label'>
               <i class='far fa-flag fa-fw'
                  title='" . __('Use in CRA', 'activity') . "'></i>";
               Dropdown::showYesNo('is_oncra',
                  (isset($self->fields['id']) && $self->fields['id']) > 0 ? $self->fields['is_oncra'] : $is_cra_default,
                  -1,
                  ['value' => 1]);
               echo '</div></td>';
               echo '</tr>';

            } else {
               echo "<input type='hidden' value='1' name='is_oncra'>";
            }
            break;
      }
   }

   static function setActivity(PlanningExternalEvent $item) {

      if (self::canCreate()) {
         global $DB;
         $extevent   = new PluginActivityPlanningExternalEvent();
         $is_exist   = $extevent->getFromDBByCrit(["planningexternalevents_id=" . $item->getID()]);
         $actiontime = '';

         if (isset($item->input['plan']['_duration'])) {
            $actiontime = $item->input['plan']['_duration'];
         } else {
            $report     = new PluginActivityReport();
             if (isset($item->input['begin']) && isset($item->input['begin'])) {
                $actiontime = $report->getActionTimeForExternalEvent($item->input['begin'], $item->input['end'], '', '', '');
             } else if (isset($item->input['plan']['begin']) && isset($item->input['plan']['end'])) {
                $actiontime = $report->getActionTimeForExternalEvent($item->input['plan']['begin'], $item->input['plan']['end'], '', '', '');
               }
         }

         if (isset($item->input['id'])
            && isset($extevent->fields['is_oncra'])) {
            $extevent->getFromDBForTask($item->input['id']);

            if (!empty($extevent->fields)) {
               $extevent->update(['id'          => $extevent->fields['id'],
                  'is_oncra'                    => isset($item->input['is_oncra']) ? $item->input['is_oncra'] :
                                                      $extevent->getField('is_oncra'),
                  'planningexternalevents_id'   => $item->input['id'],
                  'actiontime'                  => $actiontime]);
            } else if (!$is_exist) {
               $extevent->add(['is_oncra'       => isset($item->input['is_oncra']) ? $item->input['is_oncra'] : '',
                  'planningexternalevents_id'   => $item->getID(),
                  'actiontime'                  => $actiontime]);
            }
         } else {
            $is_cra_default = 0;
            $opt            = new PluginActivityOption();
            $opt->getFromDB(1);
            if ($opt) {
               $is_cra_default = $opt->fields['is_cra_default'];
            }
            if (!$is_exist && $_POST['action'] !== 'clone_event') {
               $extevent->add(['is_oncra'                    => isset($item->input['is_oncra']) ? $item->input['is_oncra'] : $is_cra_default,
                               'planningexternalevents_id'   => $item->getID(),
                               'actiontime'                  => $actiontime]);
            } else if ($_POST['action'] == 'clone_event') {
               $iterator =   $DB->request(['FROM' => 'glpi_plugin_activity_planningexternalevents',
                  'LEFT JOIN' => ['glpi_planningexternalevents' => ['FKEY' => ['glpi_planningexternalevents'     => 'id',
                     'glpi_plugin_activity_planningexternalevents' => 'planningexternalevents_id']]],
                  'WHERE' => ['planningexternalevents_id' => $_POST['event']['old_items_id']]]);
               if (count($iterator)) {
                  while ($data = $iterator->next()) {
                     $extevent->add(['is_oncra'       => $data['is_oncra'],
                        'planningexternalevents_id'   => $item->getID(),
                        'actiontime'                  => $data['actiontime']]);
                  }
               }
            }
         }
      }
   }

   static function prepareInputToUpdateWithPluginOptions($item) {

      $holiday = new PluginActivityHoliday();
      $holiday->setHolidays();

      $opt = new PluginActivityOption();
      $opt->getFromDB(1);

      $use_pairs = $opt->fields['use_pairs'];
      $use_integerschedules = $opt->fields['use_integerschedules'];
      $use_we = $opt->fields['use_weekend'];

      if ($_POST['action'] == 'update_event_times') {

         if ((isset($_POST['start']) && ($_POST['start'] != 'NULL')) && (isset($_POST['end']) && ($_POST['end'] != 'NULL'))) {
            $begin = $_POST['start'];
            $end = $_POST['end'];

            $begin_hour = date('i', strtotime($begin));
            $end_hour = date('i', strtotime($end));

            $delay = floor((strtotime($end) - strtotime($begin)) / 3600);

            if ($use_integerschedules && ($begin_hour != '00' || $end_hour != '00')) {
               Session::addMessageAfterRedirect(__('Only whole hours are allowed (no split times)', 'activity'));
               unset($item->input);
               return false;
            }

            if ($use_pairs == 1 && ($delay % 2 > 0)) {
               Session::addMessageAfterRedirect(__('Only pairs schedules are allowed', 'activity'), false, ERROR);
               unset($item->input);
               return false;
            }
         }
      } else {
         if (!isset($_POST["planningeventcategories_id"]) || $_POST["planningeventcategories_id"] == 0) {
            Session::addMessageAfterRedirect(__('Activity type is mandatory field', 'activity'), false, ERROR);
            unset($item->input);
            return false;
         }

         if (!isset($_POST["users_id"]) || $_POST["users_id"] == 0) {
            Session::addMessageAfterRedirect(__('User is mandatory field', 'activity'), false, ERROR);
            unset($item->input);
            return false;
         }
         if (PluginActivityHoliday::checkInHolidays($_POST['plan'], $holiday->getHolidays())) {
            Session::addMessageAfterRedirect(__('The chosen date is a public holiday', 'activity'), false, ERROR);
            unset($item->input);
            return false;
         }

         if ($use_we == 0) {
            $hol = new PluginActivityHoliday();
            if ($hol->isWeekend($_POST['start'], true)) {
               Session::addMessageAfterRedirect(__('The chosen begin date is on weekend', 'activity'), false, ERROR);
               unset($item->input);
               return false;
            }
            if ($hol->isWeekend($_POST['end'], false)) {
               Session::addMessageAfterRedirect(__('The chosen end date is on weekend', 'activity'), false, ERROR);
               unset($item->input);
               return false;
            }
         }
      }
   }


   static function prepareInputToAddWithPluginOptions(PlanningExternalEvent $item) {
      $holiday = new PluginActivityHoliday();
      $holiday->setHolidays();

      $opt = new PluginActivityOption();
      $opt->getFromDB(1);

      $use_pairs = $opt->fields['use_pairs'];
      $use_integerschedules = $opt->fields['use_integerschedules'];
      $use_we = $opt->fields['use_weekend'];


      if ($opt && $opt->fields['use_type_as_name'] == 1) {

         $item->input["name"] = Dropdown::getDropdownName('glpi_planningeventcategories', $item->input['planningeventcategories_id']);
      }

      if (!isset($item->input["planningeventcategories_id"]) || $item->input["planningeventcategories_id"] == 0) {
         Session::addMessageAfterRedirect(__('Activity type is mandatory field', 'activity'), false, ERROR);
         unset($item->input);
         return false;
      }

      if (!isset($item->input["users_id"]) || $item->input["users_id"] == 0) {
         Session::addMessageAfterRedirect(__('User is mandatory field', 'activity'), false, ERROR);
         unset($item->input);
         return false;
      }

      if ((isset($item->input['begin']) && ($item->input['begin'] != 'NULL')) &&
         (isset($item->input['end']) && ($item->input['end'] != 'NULL'))) {

         $begin_hour = date('i', strtotime($item->input['begin']));
         $end_hour = date('i', strtotime($item->input['end']));

         $delay = floor((strtotime($item->input['end']) - strtotime($item->input['begin'])) / 3600);

         if ($use_integerschedules && ($begin_hour != '00' || $end_hour != '00')) {
            Session::addMessageAfterRedirect(__('Only whole hours are allowed (no split times)', 'activity'));
            unset($item->input);
            return false;
         }

         if ($use_pairs == 1 && ($delay % 2 > 0)) {
            Session::addMessageAfterRedirect(__('Only pairs schedules are allowed', 'activity'), false, ERROR);
            unset($item->input);
            return false;
         }

         if (PluginActivityHoliday::checkInHolidays($item->input, $holiday->getHolidays())) {
            Session::addMessageAfterRedirect(__('The chosen date is a public holiday', 'activity'), false, ERROR);
            unset($item->input);
            return false;
         }

         if ($use_we == 0) {
            $hol = new PluginActivityHoliday();
            if ($hol->isWeekend($item->input["begin"], true)) {
               Session::addMessageAfterRedirect(__('The chosen begin date is on weekend', 'activity'), false, ERROR);
               unset($item->input);
               return false;
            }
            if ($hol->isWeekend($item->input["end"], false)) {
               Session::addMessageAfterRedirect(__('The chosen end date is on weekend', 'activity'), false, ERROR);
               unset($item->input);
               return false;
            }
         }
      }
   }


   static function activityUpdate(PlanningExternalEvent $item) {

      if (!is_array($item->input) || !count($item->input)) {
         // Already cancel by another plugin
         return false;
      }
      self::prepareInputToUpdateWithPluginOptions($item);
      self::setActivity($item);
   }

   static function activityAdd(PlanningExternalEvent $item) {

      if (!is_array($item->input) || !count($item->input)) {
         // Already cancel by another plugin
         return false;
      }

      self::setActivity($item);
   }

   static function queryAllExternalEvents($criteria) {

      $dbu = new DbUtils();
      $query = "SELECT `glpi_planningexternalevents`.`planningeventcategories_id` AS type,
                    SUM(`glpi_plugin_activity_planningexternalevents`.`actiontime`) AS total_actiontime, 
                        `glpi_planningeventcategories`.`name` AS name
                     FROM `glpi_planningexternalevents` 
                     INNER JOIN `glpi_planningeventcategories` 
                        ON (`glpi_planningeventcategories`.`id` = `glpi_planningexternalevents`.`planningeventcategories_id`)
                        INNER JOIN `glpi_plugin_activity_planningexternalevents`
                         ON (`glpi_planningexternalevents`.`id` = `glpi_plugin_activity_planningexternalevents`.`planningexternalevents_id`)";
      $query .= "WHERE (`glpi_planningexternalevents`.`begin` >= '" . $criteria["begin"] . "' 
                  AND `glpi_planningexternalevents`.`begin` <= '" . $criteria["end"] . "') ";
      $query .= "  AND `glpi_planningexternalevents`.`users_id` = '" . $criteria["users_id"] . "' "
         . $dbu->getEntitiesRestrictRequest("AND", "glpi_planningexternalevents");
      $query .= " GROUP BY `glpi_planningexternalevents`.`planningeventcategories_id` 
                 ORDER BY name";

      return $query;
   }

   static function queryManageentities($criteria) {

      $dbu = new DbUtils();
      $query = "SELECT `glpi_tickets_users`.`users_id`,
                       `glpi_entities`.`name` AS entity, 
                       `glpi_plugin_manageentities_cridetails`.`date`, 
                       `glpi_plugin_manageentities_cridetails`.`technicians`, 
                       `glpi_plugin_manageentities_cridetails`.`plugin_manageentities_critypes_id`, 
                       `glpi_plugin_manageentities_cridetails`.`withcontract`, 
                       `glpi_plugin_manageentities_cridetails`.`contracts_id`, 
                       `glpi_tickets`.`id`AS tickets_id "
         . " FROM `glpi_plugin_manageentities_cridetails` "
         . " LEFT JOIN `glpi_tickets` ON (`glpi_plugin_manageentities_cridetails`.`tickets_id` = `glpi_tickets`.`id`)"
         . " LEFT JOIN `glpi_entities` ON (`glpi_tickets`.`entities_id` = `glpi_entities`.`id`)"
         . " LEFT JOIN `glpi_tickets_users` ON (`glpi_tickets_users`.`tickets_id` = `glpi_tickets`.`id`)"
         . " LEFT JOIN `glpi_tickettasks` ON (`glpi_tickettasks`.`tickets_id` = `glpi_tickets`.`id`)"
         . " LEFT JOIN `glpi_plugin_manageentities_critechnicians` ON (`glpi_plugin_manageentities_cridetails`.`tickets_id` = `glpi_plugin_manageentities_critechnicians`.`tickets_id`) "
         . " WHERE `glpi_tickets_users`.`type` = " . Ticket::ASSIGNED . " 
                  AND (`glpi_tickettasks`.`begin` >= '" . $criteria["begin"] . "' 
                  AND `glpi_tickettasks`.`end` <= '" . $criteria["end"] . "') "
         . " AND `glpi_tickets`.`is_deleted` = 0"
         . " AND (`glpi_tickets_users`.`users_id` ='" . $criteria["users_id"] . "' OR `glpi_plugin_manageentities_critechnicians`.`users_id` ='" . $criteria["users_id"] . "') ";
      $query .= $dbu->getEntitiesRestrictRequest("AND", "glpi_tickets", '',
         $_SESSION["glpiactiveentities"], false);
      $query .= " AND `glpi_tickettasks`.`actiontime` != 0";
      $query .= " GROUP BY `glpi_plugin_manageentities_cridetails`.`tickets_id` ";
      $query .= " ORDER BY `glpi_plugin_manageentities_cridetails`.`date` ASC";

      return $query;
   }

   static function queryTickets($criteria) {

      $plugin = new Plugin();

      $query = "SELECT    `glpi_tickettasks`.*,
                          `glpi_plugin_activity_tickettasks`.`is_oncra`,
                          `glpi_entities`.`name` AS entity,
                          `glpi_entities`.`id` AS entities_id
                     FROM `glpi_tickettasks`
                     INNER JOIN `glpi_tickets`
                        ON (`glpi_tickets`.`id` = `glpi_tickettasks`.`tickets_id` AND `glpi_tickets`.`is_deleted` = 0)
                     LEFT JOIN `glpi_entities` 
                        ON (`glpi_tickets`.`entities_id` = `glpi_entities`.`id`) 
                     LEFT JOIN `glpi_plugin_activity_tickettasks` 
                        ON (`glpi_tickettasks`.`id` = `glpi_plugin_activity_tickettasks`.`tickettasks_id`) ";
      $query .= "WHERE ";
      if ($plugin->isActivated('manageentities')) {
         $query .= "`glpi_tickettasks`.`tickets_id` 
                     NOT IN (SELECT `tickets_id` 
                              FROM `glpi_plugin_manageentities_cridetails`) AND ";
      }
      $dbu = new DbUtils();
      $query .= "((`glpi_tickettasks`.`begin` >= '" . $criteria["begin"] . "' 
                           AND `glpi_tickettasks`.`end` <= '" . $criteria["end"] . "'
                           AND `glpi_tickettasks`.`users_id_tech` = '" . $criteria["users_id"] . "' " .
         $dbu->getEntitiesRestrictRequest("AND", "glpi_tickets", '',
            $_SESSION["glpiactiveentities"], false);
      $query .= "                     ) 
                           OR (`glpi_tickettasks`.`date` >= '" . $criteria["begin"] . "' 
                           AND `glpi_tickettasks`.`date` <= '" . $criteria["end"] . "'
                           AND `glpi_tickettasks`.`users_id_tech` = '" . $criteria["users_id"] . "'
                           AND `glpi_tickettasks`.`begin` IS NULL " . $dbu->getEntitiesRestrictRequest("AND", "glpi_tickets", '',
            $_SESSION["glpiactiveentities"], false);
      $query .= " )) AND `glpi_tickettasks`.`actiontime` != 0 AND `glpi_plugin_activity_tickettasks`.`is_oncra` = 1";
      $query .= " ORDER BY `glpi_tickettasks`.`begin` ASC";

      return $query;
   }

   static function queryUserExternalEvents($criteria) {

      $dbu = new DbUtils();
      $query = "SELECT `glpi_planningexternalevents`.`name` AS name,
                       `glpi_planningexternalevents`.`id` AS id,
                       `glpi_plugin_activity_planningexternalevents`.`actiontime` AS actiontime,
                       `glpi_planningexternalevents`.`text` AS text,
                       `glpi_planningeventcategories`.`name` AS type,
                       `glpi_planningexternalevents`.`begin` AS begin,
                       `glpi_planningexternalevents`.`end` AS end,
                       `glpi_planningexternalevents`.`planningeventcategories_id` AS type_id,
                       `glpi_entities`.`name` AS entity
               FROM `glpi_planningexternalevents` ";
      $query .= " LEFT JOIN `glpi_plugin_activity_planningexternalevents` 
                     ON (`glpi_planningexternalevents`.`id` = `glpi_plugin_activity_planningexternalevents`.`planningexternalevents_id`)";
      $query .= " LEFT JOIN `glpi_users` 
                     ON (`glpi_users`.`id` = `glpi_planningexternalevents`.`users_id`)";
      $query .= " LEFT JOIN `glpi_planningeventcategories` 
                     ON (`glpi_planningeventcategories`.`id` = `glpi_planningexternalevents`.`planningeventcategories_id`)";
      $query .= " LEFT JOIN `glpi_entities` 
                     ON (`glpi_planningexternalevents`.`entities_id` = `glpi_entities`.`id`)";
      $query .= " WHERE ";
      $query .= "  `glpi_planningexternalevents`.`users_id` = '" . $criteria["users_id"] . "' "
         . $dbu->getEntitiesRestrictRequest("AND", "glpi_planningexternalevents") . "
                  AND (`glpi_planningexternalevents`.`begin` >= '" . $criteria["begin"] . "' 
                  AND `glpi_planningexternalevents`.`begin` <= '" . $criteria["end"] . "') ";

      if ($criteria["is_usedbycra"]) {
         $query .= " AND `glpi_plugin_activity_planningexternalevents`.`is_oncra` ";
      }
      $query .= " AND `glpi_plugin_activity_planningexternalevents`.`actiontime` != 0";
      $query .= " ORDER BY `glpi_planningexternalevents`.`name`";

      return $query;
   }

   static function dateAdd($v, $d = null, $f = "Y-m-d") {
      $d = ($d ? $d : date("Y-m-d"));
      return date($f, strtotime($v . " days", strtotime($d)));
   }

   static function getNbDays($debut, $fin) {
      $diff = strtotime(date('Y-m-d', strtotime($fin))) - strtotime(date('Y-m-d', strtotime($debut)));

      return (round($diff / (3600 * 24)) + 1);
   }


   function getFromDBForTask($projecttasks_id) {
      $dbu = new DbUtils();
      $data = $dbu->getAllDataFromTable($this->getTable(), [$dbu->getForeignKeyFieldForTable('glpi_planningexternalevents') => $projecttasks_id]);

      $this->fields = array_shift($data);
   }

}