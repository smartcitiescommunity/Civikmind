<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 moreticket plugin for GLPI
 Copyright (C) 2013-2016 by the moreticket Development Team.

 https://github.com/InfotelGLPI/moreticket
 -------------------------------------------------------------------------

 LICENSE

 This file is part of moreticket.

 moreticket is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 moreticket is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with moreticket. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginMoreticketWaitingTicket
 */
class PluginMoreticketWaitingTicket extends CommonDBTM {

   static $types     = ['Ticket'];
   var    $dohistory = true;
   static $rightname = "plugin_moreticket";

   /**
    * Have I the global right to "create" the Object
    * May be overloaded if needed (ex KnowbaseItem)
    *
    * @return booleen
    **/
   static function canCreate() {

      if (static::$rightname) {
         return Session::haveRight(static::$rightname, UPDATE);
      }
      return false;
   }

   /**
    * functions mandatory
    * getTypeName(), canCreate(), canView()
    * @param int $nb
    * @return string|translated
    */
   public static function getTypeName($nb = 0) {

      return _n('Waiting ticket', 'Waiting tickets', $nb, 'moreticket');
   }

   /**
    * Display moreticket-item's tab for each users
    *
    * @param CommonGLPI $item
    * @param int        $withtemplate
    *
    * @return array|string
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      $config = new PluginMoreticketConfig();

      if (!$withtemplate) {
         if ($item->getType() == 'Ticket' && $config->useWaiting() == true) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               $dbu = new DbUtils();
               return self::createTabEntry(self::getTypeName(2),
                                           $dbu->countElementsInTable($this->getTable(),
                                                                ["tickets_id" => $item->getID()]));
            }
            return self::getTypeName(2);
         }
      }
      return '';
   }

   /**
    * Display tab's content for each users
    *
    * @static
    *
    * @param CommonGLPI $item
    * @param int        $tabnum
    * @param int        $withtemplate
    *
*@return bool|true
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if (in_array($item->getType(), PluginMoreticketWaitingTicket::getTypes(true))) {
         self::showForTicket($item);
      }
      return true;
   }

   /**
    * Check the mandatory values of forms
    *
    * @param      $values
    * @param bool $add
    *
    * @return bool
    */
   static function checkMandatory($values, $add = false) {

      $checkKo   = [];
      $dateError = false;

      $config = new PluginMoreticketConfig();

      $mandatory_fields = [];

      if ($config->mandatoryReportDate() == true) {
         $mandatory_fields['date_report'] = __('Postponement date', 'moreticket');
      }

      if ($config->mandatoryWaitingType() == true) {
         $mandatory_fields['plugin_moreticket_waitingtypes_id'] = PluginMoreticketWaitingType::getTypeName(1);
      }

      if ($config->mandatoryWaitingReason() == true) {
         $mandatory_fields['reason'] = __('Reason', 'moreticket');
      }

      $msg = [];

      foreach ($mandatory_fields as $key => $value) {
         if (!array_key_exists($key, $values)) {
            $msg[]     = $value;
            $checkKo[] = 1;
         }
      }

      foreach ($values as $key => $value) {
         if (array_key_exists($key, $mandatory_fields)) {
            if ($key != 'date_report' && empty($value)) {
               $msg[]     = $mandatory_fields[$key];
               $checkKo[] = 1;
            } else if ($key == 'date_report' && $value == 'NULL') {
               $msg[]     = $mandatory_fields[$key];
               $checkKo[] = 1;
            } else if ($key == 'date_report' && strtotime($value) <= time()) {
               $dateError = Html::convDateTime($value);
               $checkKo[] = 1;
            }
         }
         $_SESSION['glpi_plugin_moreticket_waiting'][$key] = $value;
      }

      if (in_array(1, $checkKo)) {
         if (!$add) {
            $errorMessage = __('Waiting ticket cannot be saved', 'moreticket') . "<br>";
         } else {
            $errorMessage = __('Ticket cannot be saved', 'moreticket') . "<br>";
         }

         if ($dateError) {
            $errorMessage .= __("Report date is inferior of today's date", 'moreticket') . " : " . $dateError . "<br>";
         }

         if (count($msg)) {
            $errorMessage .= _n('Mandatory field', 'Mandatory fields', 2) . " : " . implode(', ', $msg);
         }

         Session::addMessageAfterRedirect($errorMessage, false, ERROR);

         return false;
      }

      return true;
   }

   /**
    * Print the waiting ticket form
    *
    * @param $ID integer ID of the item
    * @param $options array
    *     - target filename : where to go when done.
    *     - withtemplate boolean : template or basic item
    *
    * @return Nothing (display)
    * */
   function showForm($ID, $options = []) {

      // validation des droits
      if (!$this->canView()) {
         return false;
      }

      if ($ID > 0) {
         if (!$this->fields = self::getWaitingTicketFromDB($ID)) {
            $this->getEmpty();
         }
      } else {
         // Create item
         $this->getEmpty();
      }

      // If values are saved in session we retrieve it
      if (isset($_SESSION['glpi_plugin_moreticket_waiting'])) {
         foreach ($_SESSION['glpi_plugin_moreticket_waiting'] as $key => $value) {
            switch ($key) {
               case 'reason':
                  $this->fields[$key] = stripslashes($value);
                  break;
               default :
                  $this->fields[$key] = $value;
                  break;
            }
         }
      }

      unset($_SESSION['glpi_plugin_moreticket_waiting']);

      $config = new PluginMoreticketConfig();

      echo "<div class='spaced' id='moreticket_waiting_ticket'>";
      echo "</br>";
      echo "<table class='moreticket_waiting_ticket' id='cl_menu'>";
      echo "<tr><td>";
      echo __('Reason', 'moreticket');
      if ($config->mandatoryWaitingReason() == true) {
         echo "&nbsp;:&nbsp;<span class='red'>*</span>&nbsp;";
      }
      Html::autocompletionTextField($this, "reason");
      echo "</td></tr>";
      echo "<tr><td>";
      echo PluginMoreticketWaitingType::getTypeName(1);
      if ($config->mandatoryWaitingType() == true) {
         echo "&nbsp;:&nbsp;<span class='red'>*</span>&nbsp;";
      }
      $opt = ['value' => $this->fields['plugin_moreticket_waitingtypes_id']];
      Dropdown::show('PluginMoreticketWaitingType', $opt);
      echo "</td></tr>";
      echo "<tr><td>";
      echo __('Postponement date', 'moreticket');

      if ($config->mandatoryReportDate() == true) {
         echo "&nbsp;:&nbsp;<span class='red'>*</span>&nbsp;";
      }
      if ($this->fields['date_report'] == 'NULL') {
         $this->fields['date_report'] = date("Y-m-d H:i:s");
      }
      Html::showDateTimeField("date_report", ['value'      => $this->fields['date_report'],
                                              'maybeempty' => false]);

      echo "</td></tr>";
      echo "</table>";
      echo "</div>";
   }

   /**
    * Print the waiting ticket task form
    *
    * @param $ID integer ID of the item
    * @param $options array
    *     - target filename : where to go when done.
    *     - withtemplate boolean : template or basic item
    *
    * @return Nothing (display)
    * */
   function showFormTicketTask($ID, $options = []) {

      // validation des droits
      if (!$this->canView()) {
         return false;
      }

      if ($ID > 0) {
         if (!$this->fields = self::getWaitingTicketFromDB($ID)) {
            $this->getEmpty();
         }
      } else {
         // Create item
         $this->getEmpty();
      }

      // If values are saved in session we retrieve it
      if (isset($_SESSION['glpi_plugin_moreticket_waiting'])) {
         foreach ($_SESSION['glpi_plugin_moreticket_waiting'] as $key => $value) {
            switch ($key) {
               case 'reason':
                  $this->fields[$key] = stripslashes($value);
                  break;
               default :
                  $this->fields[$key] = $value;
                  break;
            }
         }
      }

      unset($_SESSION['glpi_plugin_moreticket_waiting']);

      $config = new PluginMoreticketConfig();

      echo "<div class='spaced' id='moreticket_waiting_ticket_task'>";
      echo "</br>";
      echo "<table class='moreticket_waiting_ticket' id='cl_menu'>";
      echo "<tr><td>";
      echo __('Reason', 'moreticket');
      if ($config->mandatoryWaitingReason() == true) {
         echo "&nbsp;:&nbsp;<span class='red'>*</span>&nbsp;";
      }
      Html::autocompletionTextField($this, "reason");
      echo "</td></tr>";
      echo "<tr><td>";
      echo PluginMoreticketWaitingType::getTypeName(1);
      if ($config->mandatoryWaitingType() == true) {
         echo "&nbsp;:&nbsp;<span class='red'>*</span>&nbsp;";
      }
      $opt = ['value' => $this->fields['plugin_moreticket_waitingtypes_id']];
      Dropdown::show('PluginMoreticketWaitingType', $opt);
      echo "</td></tr>";
      echo "<tr><td>";
      echo __('Postponement date', 'moreticket');

      if ($config->mandatoryReportDate() == true) {
         echo "&nbsp;:&nbsp;<span class='red'>*</span>&nbsp;";
      }
      if ($this->fields['date_report'] == 'NULL') {
         $this->fields['date_report'] = date("Y-m-d H:i:s");
      }
      Html::showDateTimeField("date_report", ['value'      => $this->fields['date_report'],
                                              'maybeempty' => false]);

      echo "</td></tr>";
      echo "</table>";
      echo "</div>";
   }

   /**
    * Print the wainting ticket form
    *
    * @param $item
    * @return Nothing
    * @internal param int $ID ID of the item
    * @internal param array $options - target filename : where to go when done.*     - target filename : where to go when done.
    *     - withtemplate boolean : template or basic item
    *
    */
   static function showForTicket($item) {

      // validation des droits
      if (!Session::haveRight('plugin_moreticket', READ)) {
         return false;
      }

      if (isset($_REQUEST["start"])) {
         $start = $_REQUEST["start"];
      } else {
         $start = 0;
      }

      // Total Number of events
      $dbu    = new DbUtils();
      $number = $dbu->countElementsInTable("glpi_plugin_moreticket_waitingtickets",
                                           ["tickets_id" =>$item->getField('id')]);

      if ($number < 1) {
         echo "<div class='center'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr><th>" . __('No historical') . "</th></tr>";
         echo "</table>";
         echo "</div><br>";
         return;
      } else {
         echo "<div class='center'>";
         // Display the pager
         Html::printAjaxPager(__('Ticket suspension history', 'moreticket'), $start, $number);
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr>";
         echo "<th>" . __('Suspension date', 'moreticket') . "</th>";
         echo "<th>" . __('Reason', 'moreticket') . "</th>";
         echo "<th>" . PluginMoreticketWaitingType::getTypeName(1) . "</th>";
         echo "<th>" . __('Postponement date', 'moreticket') . "</th>";
         echo "<th>" . __('Suspension end date', 'moreticket') . "</th>";
         echo "</tr>";

         foreach (self::getWaitingTicketFromDB($item->getField('id'),
                                               ['start' => $start,
                                                'limit' => $_SESSION['glpilist_limit']]) as $waitingTicket) {

            echo "<tr class='tab_bg_2'>";
            echo "<td>";
            echo Html::convDateTime($waitingTicket['date_suspension']);
            echo "</td>";
            echo "<td>";
            echo $waitingTicket['reason'];
            echo "</td>";
            echo "<td>";
            echo Dropdown::getDropdownName('glpi_plugin_moreticket_waitingtypes',
                                           $waitingTicket['plugin_moreticket_waitingtypes_id']);
            echo "</td>";
            echo "<td>";
            if ($waitingTicket['date_report'] == "0000-00-00 00:00:00") {
               echo _x('periodicity', 'None');
            } else {
               echo Html::convDateTime($waitingTicket['date_report']);
            }
            echo "</td>";
            echo "<td>";
            echo Html::convDateTime($waitingTicket['date_end_suspension']);
            echo "</td>";
            echo "</tr>";
         }

         echo "</table>";
         echo "</div>";
         Html::printAjaxPager(__('Ticket suspension history', 'moreticket'), $start, $number);
      }
   }

   /**
    * Get last waitingTicket
    *
    * @param       $tickets_id
    * @param array $options
    *
    * @return array|bool|mixed
    */
   static function getWaitingTicketFromDB($tickets_id, $options = []) {
      global $DB;

      $dbu = new DbUtils();
      if (sizeof($options) == 0) {
         $iterator = $DB->request("glpi_plugin_moreticket_waitingtickets",
                                                       '`tickets_id` = ' . $tickets_id .' 
                                                       AND `date_suspension` IN (SELECT max(`date_suspension`) 
                                                FROM `glpi_plugin_moreticket_waitingtickets` 
                                                WHERE `tickets_id` = ' . $tickets_id . ')
                 AND (UNIX_TIMESTAMP(`date_end_suspension`) = 0 OR UNIX_TIMESTAMP(`date_end_suspension`) IS NULL)');
         $data_WaitingType = [];
         while ($row = $iterator->next()) {
            $data_WaitingType[$row['id']] = $row;
         }
      } else {
         $iterator = $DB->request("glpi_plugin_moreticket_waitingtickets",
                   "tickets_id = $tickets_id 
                   ORDER BY `date_suspension` DESC 
                   LIMIT " . intval($options['start']) . "," . intval($options['limit']));

         $data_WaitingType = [];
         while ($row = $iterator->next()) {
            $data_WaitingType[$row['id']] = $row;
         }
      }

      if (sizeof($data_WaitingType) > 0) {
         if (sizeof($options) == 0) {
            $data_WaitingType = reset($data_WaitingType);
         }

         return $data_WaitingType;
      }

      return false;
   }

   /**
    * @param $item
    */
   static function preUpdateWaitingTicket($item) {
      $config = new PluginMoreticketConfig();
      if ($config->useWaiting()) {
         $waiting_ticket = new self();

         // Then we add tickets informations
         if (isset($item->fields['id'])
             && isset($item->fields['status'])
             && isset($item->input['status'])) {
            // ADD
            if ($item->fields['status'] != CommonITILObject::WAITING
                && $item->input['status'] == CommonITILObject::WAITING) {

               if (self::checkMandatory($item->input)) {
                  if (isset($item->input['date_report'])
                      && ($item->input['date_report'] == "0000-00-00 00:00:00" || empty($item->input['date_report']))) {
                     $item->input['date_report'] = 'NULL';
                  }

                  $status = (in_array($item->fields['status'],
                                      [CommonITILObject::SOLVED, CommonITILObject::CLOSED]))
                     ? CommonITILObject::ASSIGNED : $item->fields['status'];

                  // Then we add tickets informations
                  $input = ['reason'                            => (isset($item->input['reason'])) ? $item->input['reason'] : "",
                                 'tickets_id'                        => $item->fields['id'],
                                 'date_report'                       => (isset($item->input['date_report'])) ? $item->input['date_report'] : "NULL",
                                 'date_suspension'                   => date("Y-m-d H:i:s"),
                                 'date_end_suspension'               => 'NULL',
                                 'status'                            => $status,
                                 'plugin_moreticket_waitingtypes_id' => (isset($item->input['plugin_moreticket_waitingtypes_id'])) ? $item->input['plugin_moreticket_waitingtypes_id'] : 0];
                  if ($waiting_ticket->add($input)) {

                     unset($_SESSION['glpi_plugin_moreticket_waiting']);
                  }
               } else {
                  unset($item->input['status']);
               }

               // UPDATE
            } else if ($item->fields['status'] == CommonITILObject::WAITING
                       && $item->input['status'] == CommonITILObject::WAITING) {

               $waiting_ticket_data = self::getWaitingTicketFromDB($item->fields['id']);
               if (($waiting_ticket_data === false)) {
                  if (self::checkMandatory($item->input)) {
                     if (isset($item->input['date_report'])
                         && $item->input['date_report'] == "0000-00-00 00:00:00") {
                        $item->input['date_report'] = 'NULL';
                     }
                     $input = ['reason'                            => (isset($item->input['reason'])) ? $item->input['reason'] : "",
                                    'tickets_id'                        => $item->fields['id'],
                                    'date_report'                       => (isset($item->input['date_report']) && !empty($item->input['date_report'])) ? $item->input['date_report'] : "NULL",
                                    'date_suspension'                   => date("Y-m-d H:i:s"),
                                    'date_end_suspension'               => 'NULL',
                                    'plugin_moreticket_waitingtypes_id' => (isset($item->input['plugin_moreticket_waitingtypes_id'])) ? $item->input['plugin_moreticket_waitingtypes_id'] : 0];

                     // Then we add tickets informations
                     if ($waiting_ticket->add($input)) {
                        unset($_SESSION['glpi_plugin_moreticket_waiting']);
                     }
                  } else {
                     unset($item->input['status']);
                  }
               } else {
                  $waiting_ticket->update(['id'                                => $waiting_ticket_data['id'],
                                                'reason'                            => $item->input['reason'],
                                                'date_report'                       => $item->input['date_report'],
                                                'plugin_moreticket_waitingtypes_id' => $item->input['plugin_moreticket_waitingtypes_id']]);
               }
            }
         }
      }
   }

   /**
    * @param $item
    */
   static function postUpdateWaitingTicket($item) {

      $waiting_ticket = new self();
      // Then we add tickets informations
      if (isset($item->fields['id'])) {
         if (isset($item->oldvalues['status'])
             && $item->oldvalues['status'] == CommonITILObject::WAITING) {
            if (isset($item->input['status'])
                && $item->input['status'] != CommonITILObject::WAITING) {
               // Get all waiting with date_suspension < today
               $condition = ['tickets_id' => $item->fields['id'],
                             [
                                'OR' => [
                                   ['date_end_suspension' => NULL],
                                   ['date_end_suspension' => '']
                                ]
                             ]] + [new QueryExpression(
                     'UNIX_TIMESTAMP(date_suspension) <= UNIX_TIMESTAMP(NOW())'
                  )
               ];

               $lastWaiting = $waiting_ticket->find($condition);

               foreach ($lastWaiting as $field) {
                  $waiting_ticket->update(['id'                  => $field['id'],
                                           'date_end_suspension' => date("Y-m-d H:i:s")]);
               }
               unset($_SESSION['glpi_plugin_moreticket_waiting']);
            }
         }
      }
   }

   // Hook done on before add ticket - checkMandatory
   /**
    * @param $item
    * @return bool
    */
   static function preAddWaitingTicket($item) {
      if (!is_array($item->input) || !count($item->input)) {
         // Already cancel by another plugin
         return false;
      }

      $config = new PluginMoreticketConfig();
      if (isset($config->fields['use_waiting'])
          && $config->useWaiting()) {
         // Then we add tickets informations
         if (isset($item->input['id'])
             && isset($item->input['status'])
             && $item->input['status'] == CommonITILObject::WAITING
             && !self::checkMandatory($item->input, true)) {

            $_SESSION['saveInput'][$item->getType()] = $item->input;
            $item->input                             = [];
         }
      }
      return true;
   }

   // Hook done on after add ticket - add waitingtickets
   /**
    * @param $item
    * @return bool
    */
   static function postAddWaitingTicket($item) {
      if (!is_array($item->input) || !count($item->input)) {
         // Already cancel by another plugin
         return false;
      }

      $config = new PluginMoreticketConfig();
      if (isset($config->fields['use_waiting']) && $config->useWaiting()) {
         $waiting_ticket = new self();
         // Then we add tickets informations
         if (isset($item->fields['id']) && $item->input['status'] == CommonITILObject::WAITING) {
            if (self::checkMandatory($item->input)) {

               if (empty($item->input['date_report'])) {
                  $item->input['date_report'] = 'NULL';
               }
               // Then we add tickets informations
               if ($waiting_ticket->add(['reason'                            => $item->input['reason'],
                                              'tickets_id'                        => $item->fields['id'],
                                              'date_report'                       => $item->input['date_report'],
                                              'date_suspension'                   => date("Y-m-d H:i:s"),
                                              'date_end_suspension'               => 'NULL',
                                              'plugin_moreticket_waitingtypes_id' => $item->input['plugin_moreticket_waitingtypes_id']])) {

                  unset($_SESSION['glpi_plugin_moreticket_waiting']);
               }
            } else {
               $item->input['id']                       = $item->fields['id'];
               $_SESSION['saveInput'][$item->getType()] = $item->input;
               unset($item->input['status']);
            }
         }
      }
      return true;
   }

   /**
    * Type than could be linked to a typo
    *
    * @param $all boolean, all type, or only allowed ones
    *
    * @return array of types
    * */
   static function getTypes($all = false) {

      if ($all) {
         return self::$types;
      }

      // Only allowed types
      $types = self::$types;
      $dbu   = new DbUtils();

      foreach ($types as $key => $type) {
         if (!($item = $dbu->getItemForItemtype($type))) {
            continue;
         }

         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }

   /**
    * @return string
    */
   static function queryTicketWaiting() {
      $query = "SELECT `glpi_tickets`.`id` AS tickets_id
                FROM `glpi_tickets` 
                WHERE `glpi_tickets`.`status` = '" . Ticket::WAITING . "'
                AND `is_deleted` = 0";

      return $query;
   }

   /**
    * Cron action
    *
    * @global $DB
    * @global $CFG_GLPI
    * @param $task for log
    * @return int
    */
   static function cronMoreticketWaitingTicket($task = null) {
      global $DB;

      if ($task->fields["state"] == CronTask::STATE_DISABLE) {
         return 0;
      }

      $cron_status = 0;
      $today       = date('Y-m-d H:i:s');

      $waiting_ticket = new self();
      $ticket         = new Ticket();
      $log            = new Log();

      $query_ticket_waiting = self::queryTicketWaiting();
      foreach ($DB->request($query_ticket_waiting) as $data) {
         // Update ticket only if last waiting has empty end of suspension
         $waiting = $waiting_ticket->getWaitingTicketFromDB($data['tickets_id']);
         if ($waiting
             && !empty($waiting['date_report'])
             && $waiting['date_report'] <= $today
         ) {
            $ticket->update(['id'     => $data['tickets_id'],
                                  'status' => $waiting['status']]);
            $waiting_ticket->update(['id'                  => $waiting['id'],
                                          'date_end_suspension' => date("Y-m-d H:i:s")]);
            $cron_status = 1;
            $task->addVolume(1);
            if (Session::isCron()) {
               $log->history($data['tickets_id'], 'Ticket', [12, Ticket::WAITING, $waiting['status']]);
            }
         }
      }
      return $cron_status;
   }

   // Cron action
   /**
    * @param $name
    *
    * @return array
    */
   static function cronInfo($name) {

      switch ($name) {
         case 'MoreticketWaitingTicket':
            return [
               'description' => __("End of standby ticket", 'moreticket')];   // Optional
            break;
      }
      return [];
   }


   /**
    * Print the waiting ticket form
    *
    * @param $ID integer ID of the item
    * @param $options array
    *     - target filename : where to go when done.
    *     - withtemplate boolean : template or basic item
    *
    * @return Nothing (display)
    * */
   function showQuestionSign($ID, $options = []) {

      global $CFG_GLPI;
      // validation des droits
      if (!$this->canView()) {
         return false;
      }
      $ticket = new Ticket();
      if ($ID > 0) {

         $ticket->getFromDB($ID);
         if (!$this->fields = self::getWaitingTicketFromDB($ID)) {
            $this->getEmpty();
         }
      } else {
         // Create item
         $ticket->getEmpty();
         $this->getEmpty();
      }




      $config = new PluginMoreticketConfig();

      echo "<div class='spaced' id='isQuestion' style=\"
             word-wrap: normal;
             white-space: normal;
             display: block;
             clear: both;
             text-align: center;
             margin-left: -50px;
         \">";
      echo "</br>";
      echo "<table class='moreticket_waiting_ticket' id='c2_menu'>";
      echo "<tr><td>";
      echo __('Ticket waiting',"moreticket");
//      if ($config->mandatoryWaitingReason() == true) {
//         echo "&nbsp;:&nbsp;<span class='red'>*</span>&nbsp;";
//      }
      self::showSwitchField("question", 1);

      Ajax::updateItemOnEvent("question","fakeupdate",$CFG_GLPI["root_doc"].PLUGIN_MORETICKET_DIR_NOFULL."/ajax/updatestatus.php",["question"=>'__VALUE__',"status"=>$ticket->getField("status")]);
      Ajax::updateItem("fakeupdate",$CFG_GLPI["root_doc"].PLUGIN_MORETICKET_DIR_NOFULL."/ajax/updatestatus.php",["question"=>'1',"status"=>$ticket->getField("status")]);

      echo "</td></tr>";

      echo "</table>";
      echo "<div id='fakeupdate'></div>";
      echo "</div>";
   }

   function showSwitchField($name, $value) {

      echo Html::hidden($name, ['id'    => $name,
         'value' => $value]);
      echo Html::scriptBlock("(function(){
                             var toggleButton = $('.$name');
                             toggleButton.click(function() {
                             if ($(this).hasClass('fa-toggle-on')) {
                                   toggleButton.removeClass('fa-toggle-on');
                                   toggleButton.addClass('fa-toggle-off');
                                   toggleButton.removeClass('enabled');
                                   toggleButton.addClass('disabled');
                                   document.getElementById('$name').value = '0';
                                   var event = new Event('change');
                                   document.getElementById('$name').dispatchEvent(event);
                                 } else {
                                   toggleButton.removeClass('fa-toggle-off');
                                   toggleButton.addClass('fa-toggle-on');
                                   toggleButton.removeClass('disabled');
                                   toggleButton.addClass('enabled');
                                   document.getElementById('$name').value = '1';
                                   var event = new Event('change');
                                   document.getElementById('$name').dispatchEvent(event);
                                 }
                             });
                           })();");
      if ($value == 1) {
         echo "<a class=\"button\"><i class=\"$name fa-fw fas fa-2x fa-toggle-on enabled\"></i></a>";
      } else {
         echo "<a class=\"button\"><i class=\"$name fa-fw fas fa-2x fa-toggle-off disabled\"></i></a>";
      }
   }

}
