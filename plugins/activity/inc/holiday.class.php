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

class PluginActivityHoliday extends CommonDBTM {

   var    $dohistory = false;
   var    $holidays  = [];
   static $rightname = "plugin_activity";

   // From CommonDBTM
   public $auto_message_on_action = false;

   // From CommonDBChild
   static public $itemtype = 'PluginActivityHoliday';
   static public $items_id = 'plugin_activity_holidays_id';

   static public $log_history_add    = Log::HISTORY_LOG_SIMPLE_MESSAGE;
   static public $log_history_update = Log::HISTORY_LOG_SIMPLE_MESSAGE;
   static public $log_history_delete = Log::HISTORY_LOG_SIMPLE_MESSAGE;

   /**
    * functions mandatory
    * getTypeName(), canCreate(), canView()
    * */
   static function getTypeName($nb = 1) {
      return _n('Private holiday', 'Private holidays', $nb, 'activity');
   }

   static function canView() {
      return Session::haveRight('plugin_activity_can_requestholiday', 1);
   }

   function canViewItem() {
      return Session::haveRight('plugin_activity_can_requestholiday', 1);
   }

   static function canCreate() {
      return Session::haveRight('plugin_activity_can_requestholiday', 1);
   }

   function canCreateItem() {
      return Session::haveRight('plugin_activity_can_requestholiday', 1);
   }

   function cleanDBonPurge() {
      $holidayValidation = new PluginActivityHolidayValidation();
      $holidayValidation->cleanDBonItemDelete($this->getType(), $this->fields['id']);

      parent::cleanDBonPurge();
   }

   function post_getEmpty() {

      $this->fields['is_planned'] = 1;
   }

   function defineTabs($options = []) {
      $ong = [];
      $this->addDefaultFormTab($ong);
      $this->addStandardTab("PluginActivityHolidayValidation", $ong, $options);
      $this->addStandardTab(__CLASS__, $ong, $options);
      return $ong;
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      return __('Link');
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if ($item->getType() == "PluginActivityHolidayValidation") {
         if ($tabnum == 0) {
            $validation = new PluginActivityHolidayValidation();
            $validation->showSummary($item);
         }
      } else {
         $item->showLinks($item);
      }
      return true;
   }


   private function getSavedValues() {
      if (isset($_SESSION['saved_values']) && $_SESSION['saved_values'] != "") {
         $input = $_SESSION['saved_values'];
         unset($_SESSION['saved_values']);
         return $input;
      } else {
         return false;
      }
   }

   private function saveValues($input) {
      $_SESSION['saved_values'] = $input;
   }

   /**
    * @see CommonDBTM::prepareInputForAdd()
    **/
   function prepareInputForAdd($input) {

      $AllDay = PluginActivityReport::getAllDay();

      $this->setHolidays();

      $input["begin"]      = date('Y-m-d', strtotime($input["begin"]));
      $input["end"]        = date('Y-m-d', strtotime($input["end"]));
      $input['actiontime'] = $input['actiontime'] * $AllDay;

      $input = $this->prepareDates($input);

      $this->saveValues($input);

      if (!isset($input['begin']) || $input['begin'] == "") {
         Session::addMessageAfterRedirect(__('Please fill a begin date', 'activity'), false, ERROR);
         return false;
      }
      if (!isset($input['end']) || $input['end'] == "") {
         Session::addMessageAfterRedirect(__('Please fill an end date', 'activity'), false, ERROR);
         return false;
      }

      if (!isset($input["plugin_activity_holidaytypes_id"])
          || $input["plugin_activity_holidaytypes_id"] == 0) {
         Session::addMessageAfterRedirect(__('Holiday type is mandatory field', 'activity'), false, ERROR);
         return false;

      } else {
         $holidayType = new PluginActivityHolidayType();
         $holidayType->getFromDB($input["plugin_activity_holidaytypes_id"]);
         if ($holidayType->fields['mandatory_comment'] == 1 && $input['comment'] == "") {
            $message = sprintf(__("You have to fill a comment for the holiday type '%s'"), $holidayType->fields['name']);
            Session::addMessageAfterRedirect($message, false, ERROR);
            return false;
         }
         if ($holidayType->fields['auto_validated'] == 1) {
            $input['global_validation'] = PluginActivityCommonValidation::ACCEPTED;
         }
      }

      $input["name"] = Dropdown::getDropdownName('glpi_plugin_activity_holidaytypes',
                                                 $input['plugin_activity_holidaytypes_id']);

      $opt = new PluginActivityOption();
      $opt->getFromDB(1);
      if ($opt) {
         $input["validation_percent"] = $opt->fields['default_validation_percent'];
      }
      if (!isset($input['global_validation'])) {
         $input['global_validation'] = PluginActivityCommonValidation::NONE;
      }

      if (!isset($input["users_id"])
          || $input["users_id"] == 0) {
         Session::addMessageAfterRedirect(__('User is mandatory field', 'activity'), false, ERROR);
         return false;
      }

      if (isset($input["actiontime"])) {

         $values = [];
         $report = new PluginActivityReport();
         $values = $report->timeRepartition($input['actiontime'] / $AllDay, $input["begin"], $values, PluginActivityReport::$WORK, $input['plugin_activity_holidaytypes_id'], $this->getHolidays());

         foreach ($values as $k => $v) {
            foreach ($v as $key => $val) {
               foreach ($val as $begin => $duration) {
                  $action       = $duration * $AllDay;
                  $input["end"] = date("Y-m-d H:i:s", strtotime($begin) + $action);
               }
            }
         }
      }

      if (isset($input["users_id"])) {
         $isAlreadyPlanned = Planning::checkAlreadyPlanned($input["users_id"], $input["begin"], $input["end"]);
         if ($isAlreadyPlanned) {
            return false;
         }
      }

      if ($this->isWeekend($input['begin'], true)) {
         Session::addMessageAfterRedirect(__('The chosen begin date is on weekend', 'activity'), false, ERROR);
         return false;
      }

      $this->setHolidays();

      if ($this->checkInHolidays($input, $this->getHolidays())) {
         Session::addMessageAfterRedirect(__('The chosen date is a public holiday', 'activity'), false, ERROR);
         return false;
      }

      if (self::userHasPlannedHolidays($input['begin'], $input['end'], $input['users_id'])) {
         if (Session::getLoginUserID() == $input['users_id']) {
            $message = __('You have already planned holidays for this period', 'activity');
         } else {
            $message = __('The user has already planned holidays for this period', 'activity');
         }
         Session::addMessageAfterRedirect($message, false, ERROR);
         return false;
      }

      $this->reinitSavedValues();
      return parent::prepareInputForAdd($input);
   }


   private function reinitSavedValues() {
      unset($_SESSION['saved_values']);
   }

   function post_addItem() {
      global $CFG_GLPI;

      $use_groupmanager = 0;
      $opt              = new PluginActivityOption();
      $opt->getFromDB(1);
      if ($opt) {
         $use_groupmanager = $opt->fields['use_groupmanager'];
      }
      $dbu     = new DbUtils();
      $user_id = $this->fields['users_id'];
      if ($use_groupmanager == 0) {

         $datas = $dbu->getAllDataFromTable("glpi_plugin_activity_preferences", ["users_id" => $user_id]);
      } else {
         $datas      = [];
         $groupusers = Group_User::getUserGroups($user_id);
         $groups     = [];
         foreach ($groupusers as $groupuser) {
            $groups[] = $groupuser["id"];
         }

         $restrict = ["groups_id"  => [implode(',', $groups)],
                      "is_manager" => 1,
                      "NOT"        => ["users_id" => $user_id]];
         $managers = $dbu->getAllDataFromTable('glpi_groups_users', $restrict);

         foreach ($managers as $manager) {
            $datas[]['users_id_validate'] = $manager['users_id'];
         }
      }

      if (sizeof($datas) > 0) {
         foreach ($datas as $data) {
            $holidayValidation                                        = new PluginActivityHolidayValidation();
            $holidayValidation->fields['plugin_activity_holidays_id'] = $this->fields['id'];
            $holidayValidation->fields['status']                      = PluginActivityCommonValidation::WAITING;

            if ($this->fields['global_validation'] == PluginActivityCommonValidation::ACCEPTED) {
               $holidayValidation->fields['status']             = PluginActivityCommonValidation::ACCEPTED;
               $holidayValidation->fields['comment_validation'] = __('Auto-validated', 'activity');
            }

            $holidayValidation->fields['users_id_validate'] = $data['users_id_validate'];
            $holidayValidation_id                           = $holidayValidation->add($holidayValidation->fields);

            $mailsend = false;
            // Send mail for each validator
            if ($CFG_GLPI["notifications_mailing"]
                && $this->fields["global_validation"] != PluginActivityCommonValidation::ACCEPTED) {
               $options  = ['plugin_activity_holidayvaldiations_id' => $holidayValidation_id,
                            'validation_id'                         => $this->fields["id"],
                            'validation_status'                     => $this->fields["global_validation"]];
               $mailsend = NotificationEvent::raiseEvent('newvalidation', $this, $options);
            }

            if ($mailsend) {
               $user = new User();
               $user->getFromDB($holidayValidation->fields["users_id_validate"]);
               $email = $user->getDefaultEmail();
               if (!empty($email)) {
                  //TRANS: %s is the user name
                  Session::addMessageAfterRedirect(sprintf(__('Approval request send to %s'), $user->getName()));
               } else {
                  Session::addMessageAfterRedirect(sprintf(__('The selected user (%s) has no valid email address. The request has been created, without email confirmation.'),
                                                           $user->getName()),
                                                   false, ERROR);
               }
            } else if ($this->fields["global_validation"] == PluginActivityCommonValidation::ACCEPTED) {
               Session::addMessageAfterRedirect(__('Validation of your holiday request', 'activity'));
            }
         }
      } else {
         Session::addMessageAfterRedirect(__("The user don't have any manager filled in his personal settings.", 'activity'), false, ERROR);
         return false;
      }

      return parent::post_addItem();
   }

   function setHolidays() {
      $this->holidays = self::getCalendarHolidaysArray($_SESSION["glpiactive_entity"]);
   }

   function getHolidays() {
      return $this->holidays;
   }

   private function prepareDates($input) {

      $hb = date('Y-m-d', strtotime($input["begin"]));
      $he = date('Y-m-d', strtotime($input["end"]));

      $hlfDayBegin = $input['radio_cb_begindate'];
      if (!isset($input['radio_cb_enddate'])) {
         $hlfDayEnd = $he . ' ' . PluginActivityReport::getPmEnd();
      } else {
         $hlfDayEnd = $input['radio_cb_enddate'];
      }

      // Same date
      if ($input["begin"] == $input["begin"]) {
         if ($hlfDayBegin == PluginActivityReport::$PM_LABEL) {
            $input['begin'] = $hb . ' ' . PluginActivityReport::getAmBegin();
         } else {
            $input['begin'] = $hb . ' ' . PluginActivityReport::getAmBegin();
         }
         $input['end'] = $he . ' ' . PluginActivityReport::getPmEnd();
      }

      switch ($hlfDayEnd) {
         case PluginActivityReport::$AM_LABEL:
            $input["end"] = $he . ' ' . PluginActivityReport::$AM_END;
            break;
         case PluginActivityReport::$PM_LABEL:
         case PluginActivityReport::$ALL_DAY_LABEL:
            $input["end"] = $he . ' ' . PluginActivityReport::getPmEnd();
            break;
      }

      switch ($hlfDayBegin) {
         case PluginActivityReport::$ALL_DAY_LABEL:
         case PluginActivityReport::$AM_LABEL:
            $input["begin"] = $hb . ' ' . PluginActivityReport::getAmBegin();
            break;
         case PluginActivityReport::$PM_LABEL:
            $input["begin"] = $hb . ' ' . PluginActivityReport::$PM_BEGIN;
            break;
      }
      return $input;
   }


   static function getActionsOn() {
      global $CFG_GLPI;

      $use_groupmanager = 0;
      $options          = new PluginActivityOption();
      $options->getFromDB(1);
      if ($options) {
         $use_groupmanager = $options->fields['use_groupmanager'];
      }

      $dbu     = new DbUtils();
      $user_id = Session::getLoginUserID();
      if ($use_groupmanager == 0) {

         $have_manager      = $dbu->getAllDataFromTable("glpi_plugin_activity_preferences", ["users_id" => $user_id]);
         $users_id_validate = $dbu->getAllDataFromTable("glpi_plugin_activity_preferences", ["users_id_validate" => $user_id]);

      } else {

         $groupusers = Group_User::getUserGroups($user_id);
         $groups     = [];
         foreach ($groupusers as $groupuser) {
            $groups[] = $groupuser["id"];
         }

         $restrict          = ["groups_id"  => [implode(',', $groups)],
                               "is_manager" => 1,
                               "users_id"   => $user_id];
         $users_id_validate = $dbu->getAllDataFromTable('glpi_groups_users', $restrict);

         $restrict     = ["groups_id"  => [implode(',', $groups)],
                          "is_manager" => 1,
                          "NOT"        => ["users_id" => $user_id]];
         $have_manager = $dbu->getAllDataFromTable('glpi_groups_users', $restrict);

      }

      $opt['criteria'][0]['field']      = 7; // Search options
      $opt['criteria'][0]['searchtype'] = 'equals';
      $opt['criteria'][0]['value']      = PluginActivityCommonValidation::WAITING;
      $opt['criteria'][1]['link']       = "AND";
      $opt['criteria'][1]['field']      = 11; // Search options
      $opt['criteria'][1]['searchtype'] = 'equals';
      $opt['criteria'][1]['value']      = Session::getLoginUserID();
      $opt['itemtype']                  = 'PluginActivityHoliday';
      $opt['start']                     = 0;

      $target = Toolbox::getItemTypeSearchURL('PluginActivityCra');
      $url    = $target . "?" . Toolbox::append_params($opt, '&amp;');

      // Array of action user can do :
      //    link     -> url of link
      //    img      -> ulr of the img to show
      //    label    -> label to show
      //    onclick  -> if set, set the onclick value of the href
      //    rights   -> if true, action shown

      $listActions = [
         PluginActivityActions::HOLIDAY_REQUEST => [
            'link'    => "#",
            'onclick' => '$(function() {' . Html::jsGetElementbyID('holiday') . ".dialog('open'); return false; });",
            'img'     => "fas fa-user-clock",
            'label'   => __('Create a holiday request', 'activity'),
            'rights'  => Session::haveRight("plugin_activity_can_requestholiday", READ) && sizeof($have_manager) > 0,
         ],

         PluginActivityActions::LIST_HOLIDAYS    => [
            'link'   => $CFG_GLPI["root_doc"] . "/plugins/activity/front/holiday.php",
            'img'    => "fas fa-search",
            'label'  => __('List of holidays', 'activity'),
            'rights' => Session::haveRight("plugin_activity_can_requestholiday", READ),
         ],
         PluginActivityActions::APPROVE_HOLIDAYS => [
            'link'   => $url,
            'img'    => "fas fa-user-check",
            'label'  => _n('Approve holiday', 'Approve holidays', 2, 'activity'),
            'rights' => Session::haveRight("plugin_activity_can_validate", READ) && sizeof($users_id_validate) > 0,
         ],
         PluginActivityActions::HOLIDAY_COUNT    => [
            'link'   => $CFG_GLPI["root_doc"] . "/plugins/activity/front/holidaycount.php",
            'img'    => "far fa-clock",
            'label'  => _n('Holiday counter', 'Holiday counters', 2, 'activity'),
            'rights' => Session::haveRight("plugin_activity_can_requestholiday", READ),
         ]
      ];

      $add = [];
      if (sizeof($have_manager) < 1) {
         $add = [
            PluginActivityActions::MANAGER => [
               'link'   => $CFG_GLPI["root_doc"] . "/front/preference.php?glpi_tab=PluginActivityPreference$1",
               'img'    => "fas fa-user-tie",
               'label'  => __('Add a manager', 'activity'),
               'rights' => Session::haveRight("plugin_activity_can_requestholiday", READ),
            ]
         ];
      }

      if (sizeof($add) > 0) {
         $listActions = array_merge($listActions, $add);
      }
      return $listActions;
   }

   /**
    * Get default values to search engine to override
    **/
   static function getDefaultSearchRequest() {

      $search = ['sort'  => 4,
                 'order' => 'DESC'];

      return $search;
   }

   function rawSearchOptions() {

      $holidaytype = new PluginActivityHolidayType();

      $tab[] = [
         'id'   => 'common',
         'name' => self::getTypeName(1)
      ];

      $tab[] = [
         'id'            => '1',
         'table'         => $this->getTable(),
         'field'         => 'name',
         'name'          => __('Name'),
         'datatype'      => 'itemlink',
         'itemlink_type' => $this->getType()
      ];

      $tab[] = [
         'id'            => '3',
         'table'         => $holidaytype->getTable(),
         'field'         => 'name',
         'name'          => PluginActivityHolidayType::getTypeName(1),
         'datatype'      => 'dropdown',
         'massiveaction' => false,
      ];

      $tab[] = [
         'id'            => '4',
         'table'         => $this->getTable(),
         'field'         => 'begin',
         'massiveaction' => false,
         'name'          => __('Begin date'),
         'datatype'      => 'datetime'
      ];

      $tab[] = [
         'id'            => '5',
         'table'         => $this->getTable(),
         'field'         => 'end',
         'massiveaction' => false,
         'name'          => __('End date'),
         'datatype'      => 'datetime'
      ];

      $tab[] = [
         'id'            => '6',
         'table'         => $this->getTable(),
         'field'         => 'is_planned',
         'massiveaction' => false,
         'name'          => __('Add to schedule'),
         'datatype'      => 'bool'
      ];

      $tab[] = [
         'id'         => '7',
         'table'      => $this->getTable(),
         'field'      => 'global_validation',
         'searchtype' => ['equals', 'notequals'],
         'name'       => __('Approval'),
         'datatype'   => 'specific'
      ];

      $tab[] = [
         'id'       => '8',
         'table'    => $this->getTable(),
         'field'    => 'comment',
         'name'     => __('Comments'),
         'datatype' => 'text'
      ];

      $tab[] = [
         'id'            => '9',
         'table'         => 'glpi_users',
         'field'         => 'name',
         'name'          => _n('User', 'Users', 1),
         'massiveaction' => false,
         'datatype'      => 'dropdown',
         'right'         => 'interface',
      ];

      $tab[] = [
         'id'            => '10',
         'table'         => $this->getTable(),
         'field'         => 'actiontime',
         'name'          => __('Total duration'),
         'nosearch'      => true,
         'massiveaction' => false,
         'datatype'      => 'specific'
      ];

      $tab[] = [
         'id'            => '11',
         'table'         => 'glpi_plugin_activity_holidayvalidations',
         'field'         => 'users_id_validate',
         'name'          => __('Approver'),
         'massiveaction' => false,
         'datatype'      => 'specific',
         'searchtype'    => ['equals', 'notequals'],
         'right'         => 'interface',
         'forcegroupby'  => true,
         'joinparams'    => [
            'jointype' => 'child'
         ]
      ];

      $tab[] = [
         'id'            => '12',
         'table'         => $this->getTable(),
         'field'         => 'date_mod',
         'massiveaction' => false,
         'name'          => __('Last update'),
         'datatype'      => 'datetime'
      ];

      $tab[] = [
         'id'            => '13',
         'table'         => 'glpi_plugin_activity_holidayperiods',
         'field'         => 'name',
         'name'          => PluginActivityHolidayPeriod::getTypeName(),
         'datatype'      => 'dropdown',
         'massiveaction' => false,
      ];

      $tab[] = [
         'id'       => '30',
         'table'    => $this->getTable(),
         'field'    => 'id',
         'name'     => __('ID'),
         'datatype' => 'number'
      ];

      $tab[] = [
         'id'   => 'validation',
         'name' => __('Approval')
      ];

      $tab[] = [
         'id'            => '51',
         'table'         => $this->getTable(),
         'field'         => 'validation_percent',
         'massiveaction' => false,
         'name'          => __('Minimum validation required'),
         'datatype'      => 'number',
         'unit'          => '%',
         'min'           => 0,
         'max'           => 100,
         'step'          => 50
      ];


      return $tab;
   }

   static function getSpecificValueToDisplay($field, $values, array $options = []) {
      return PluginActivityCommonValidation::getSpecificValueToDisplay($field, $values, $options);
   }


   static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = []) {

      $dbu = new DbUtils();
      if (!is_array($values)) {
         $values = [$field => $values];
      }
      $options['display'] = false;
      switch ($field) {
         case 'global_validation' :
            $options['name']  = $name;
            $options['value'] = $values[$field];
            $options['id']    = '';
            return PluginActivityCommonValidation::dropdownStatus($name, $options);

         case 'users_id_validate' :
            $options['name']   = $name;
            $options['value']  = $values[$field];
            $holidayValidation = new PluginActivityHolidayValidation();
            $validators        = $holidayValidation->find();
            $elements          = [Dropdown::EMPTY_VALUE];
            foreach ($validators as $validator) {
               $elements[$validator['users_id_validate']] = $dbu->getUserName($validator['users_id_validate']);
            }

            return Dropdown::showFromArray($name, $elements, ['display' => false]);
      }

      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }

   public function generateTXTfile($idHoliday) {

      $holidays = new PluginActivityHoliday();
      $holidays->getFromDB($idHoliday);

      $user = new User();
      $user->getFromDB($holidays->fields['users_id']);

      $holidayType = new PluginActivityHolidayType();
      $holidayType->getFromDB($holidays->fields['plugin_activity_holidaytypes_id']);

      $location = new Location();
      $location->getFromDB($user->fields['locations_id']);

      // Validator list
      $use_groupmanager = 0;
      $opt              = new PluginActivityOption();
      $opt->getFromDB(1);
      if ($opt) {
         $use_groupmanager = $opt->fields['use_groupmanager'];
      }

      $dbu     = new DbUtils();
      $user_id = $user->fields['id'];
      if ($use_groupmanager == 0) {

         $datas = $dbu->getAllDataFromTable("glpi_plugin_activity_preferences", ["users_id" => $user_id]);
      } else {
         $datas      = [];
         $groupusers = Group_User::getUserGroups($user_id);
         $groups     = [];
         foreach ($groupusers as $groupuser) {
            $groups[] = $groupuser["id"];
         }

         $restrict = ["groups_id"  => [implode(',', $groups)],
                      "is_manager" => 1,
                      "NOT"        => ["users_id" => $user_id]];
         $managers = $dbu->getAllDataFromTable('glpi_groups_users', $restrict);
         foreach ($managers as $manager) {
            $datas['users_id_validate'] = $manager['users_id'];
         }
      }

      $validatorList      = "";
      $validatorListArray = [];

      if (sizeof($datas) > 0) {
         foreach ($datas as $data) {
            $tmpUserMail = new UserEmail();
            $tmpUserMail->getFromDBByCrit(['users_id'   => $data['users_id_validate'],
                                           'is_default' => 1]);
            if (!in_array($tmpUserMail->fields['email'], $validatorListArray)) {
               if (strlen($validatorList) == 0) {
                  $validatorList .= $tmpUserMail->fields['email'];
               } else {
                  $validatorList .= ", " . $tmpUserMail->fields['email'];
               }
               $validatorListArray [] = $tmpUserMail->fields['email'];
            }

         }
      }

      $finalDateBegin = date('d/m/Y', strtotime($holidays->fields['begin']));
      $finalDateEnd   = date('d/m/Y', strtotime($holidays->fields['end']));

      if ($finalDateBegin == $finalDateEnd) {
         $finalDateEnd = "";
      }

      $period = $this->getPeriodForTemplate($holidays->fields['actiontime']);

      $src      = "../files/templates/";
      $filename = 'holidays_template.txt';

      $rows    = file($src . $filename);
      $rows[0] = trim($rows[0]);

      $finalRows = '';

      foreach ($rows as $row => $data) {
         $dateBegin = date('d/m/Y', strtotime($holidays->fields['begin'])) . " " . $period['begin'];

         if ($finalDateEnd != "") {
            $dateEnd = date('d/m/Y', strtotime($holidays->fields['end'])) . " " . $period['end'];
         } else {
            $dateEnd = '';
         }

         //get row data
         if (strpos($data, "{{user_firstname}}")) {
            $finalRows .= str_ireplace("{{user_firstname}}", strtoupper($user->fields['firstname']), $data);
         } else if (strpos($data, "{{user_validate}}")) {
            $finalRows .= str_ireplace("{{user_validate}}", strtoupper($validatorList), $data);
         } else if (strpos($data, "{{user_realname}}")) {
            $finalRows .= str_ireplace("{{user_realname}}", strtoupper($user->fields['realname']), $data);
         } else if (strpos($data, "{{user_matricule}}")) {
            $nb        = substr($user->fields['registration_number'], 1);
            $finalRows .= str_ireplace("{{user_matricule}}", $nb, $data);
         } else if (strpos($data, "{{nb_days}}")) {
            $finalRows .= str_ireplace("{{nb_days}}", ($holidays->fields['actiontime'] / PluginActivityReport::getAllDay()), $data);
         } else if (strpos($data, "{{date_begin}}")) {
            $finalRows .= str_ireplace("{{date_begin}}", $dateBegin, $data);
         } else if (strpos($data, "{{date_end}}")) {
            $finalRows .= str_ireplace("{{date_end}}", $dateEnd, $data);
         } else if (strpos($data, "{{holidays_shortname}}")) {
            $finalRows .= str_ireplace("{{holidays_shortname}}", $holidayType->fields['short_name'], $data);
         } else if (strpos($data, "{{holidays_comment}}")) {

            $finalRows .= str_ireplace("{{holidays_comment}}", "\n" . $holidays->fields['comment'], $data);
         } else {
            $finalRows .= $data;
         }
      }

      return $finalRows;
   }

   private function checkUserIsManager($users_id = 0) {

      $use_groupmanager = 0;
      $opt              = new PluginActivityOption();
      $opt->getFromDB(1);
      if ($opt) {
         $use_groupmanager = $opt->fields['use_groupmanager'];
      }
      $dbu = new DbUtils();
      if ($use_groupmanager == 0) {

         $datas = $dbu->getAllDataFromTable("glpi_plugin_activity_preferences", ["users_id" => $users_id, "users_id_validate" => Session::getLoginUserID()]);
      } else {
         $datas      = [];
         $groupusers = Group_User::getUserGroups($users_id);
         $groups     = [];
         foreach ($groupusers as $groupuser) {
            $groups[] = $groupuser["id"];
         }

         $restrict = ["groups_id"  => [implode(',', $groups)],
                      "is_manager" => 1];
         $managers = $dbu->getAllDataFromTable('glpi_groups_users', $restrict);

         foreach ($managers as $manager) {
            if ($manager['users_id'] == Session::getLoginUserID()) {
               $datas['users_id_validate'] = $manager['users_id'];
            }
         }
      }

      if (sizeof($datas) == 0) {

         return false;
      } else {
         return true;
      }
   }

   private function checkUserHasManager() {
      global $CFG_GLPI;

      $use_groupmanager = 0;
      $opt              = new PluginActivityOption();
      $opt->getFromDB(1);
      if ($opt) {
         $use_groupmanager = $opt->fields['use_groupmanager'];
      }
      $dbu     = new DbUtils();
      $user_id = Session::getLoginUserID();
      if ($use_groupmanager == 0) {

         $datas = $dbu->getAllDataFromTable("glpi_plugin_activity_preferences", ["users_id" => $user_id]);
      } else {
         $datas      = [];
         $groupusers = Group_User::getUserGroups($user_id);
         $groups     = [];
         foreach ($groupusers as $groupuser) {
            $groups[] = $groupuser["id"];
         }

         $restrict = ["groups_id"  => [implode(',', $groups)],
                      "is_manager" => 1,
                      "NOT"        => ["users_id" => $user_id]];
         $managers = $dbu->getAllDataFromTable('glpi_groups_users', $restrict);
         foreach ($managers as $manager) {
            $datas['users_id_validate'] = $manager['users_id'];
         }
      }

      if (sizeof($datas) == 0) {
         $url     = $CFG_GLPI["root_doc"] . "/front/preference.php?glpi_tab=PluginActivityPreference$1";
         $urlHtml = "<a href='" . $url . "' target='_blank' title='" . __('My settings') . "' >" . __('My settings') . "</a>";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1'>";
         echo "<th>" . __('Warning') . " !</th>";
         echo "</tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td>";
         echo __("You don't have any manager filled in you personal settings.", 'activity');
         echo "<br/>";
         echo __("In order to make a holiday demand, please fill at least one manager in your personal settings", 'activity');
         echo "<br/>";
         echo "<br/>";
         echo __("See the page", 'activity');
         echo " " . $urlHtml;
         echo "</td>";
         echo "</tr>";

         echo "</table>";

         return false;
      } else {
         return true;
      }
   }

   public function getPeriodForTemplate($at) {

      $actionTime = $at / PluginActivityReport::getAllDay();

      // default values
      $arrayRet = ['begin' => '', 'end' => '', 'txt' => '', 'lang' => ''];

      if ($actionTime > 0) {
         $isHalfPeriod = ($actionTime % 1 == 0) ? true : false;

         $isSameDate = date('Y-m-d', strtotime($this->fields['begin'])) == date('Y-m-d', strtotime($this->fields['end']));

         $momentDeb = date('H:i:s', strtotime($this->fields['begin']));
         $momentEnd = date('H:i:s', strtotime($this->fields['end']));

         if ($momentDeb == PluginActivityReport::getAmBegin() && $momentEnd == PluginActivityReport::getPmEnd() && !$isSameDate) {
            // begin morning +  end all day +  !same day
            $arrayRet['begin'] = '';
            $arrayRet['end']   = '';
            $arrayRet['txt']   = '';
            $arrayRet['lang']  = '';

         } else if (strtotime($momentDeb) == strtotime(PluginActivityReport::getAmBegin()) && strtotime($momentEnd) == strtotime(PluginActivityReport::getPmEnd()) && $isSameDate && $actionTime == 1) {
            // begin allday +  sameday
            $arrayRet['begin'] = '';
            $arrayRet['end']   = '';
            $arrayRet['txt']   = '';
            $arrayRet['lang']  = '';

         } else if ($momentDeb == PluginActivityReport::$PM_BEGIN && strtotime($momentEnd) == strtotime(PluginActivityReport::getPmEnd()) && $isSameDate) {
            // begin afternoon  +  sameday
            $arrayRet['begin'] = 'a';
            $arrayRet['end']   = '';
            $arrayRet['txt']   = ' a';
            $arrayRet['lang']  = __('Only on morning', 'activity');

         } else if ($momentDeb == PluginActivityReport::$PM_BEGIN && $momentEnd == PluginActivityReport::$AM_END) {
            // begin afternoon / end afternoon
            $arrayRet['begin'] = 'a';
            $arrayRet['end']   = 'm';
            $arrayRet['txt']   = ' a';
            $arrayRet['lang']  = __('Only on morning', 'activity');

         } else if (strtotime($momentDeb) == strtotime(PluginActivityReport::getAmBegin()) && $momentEnd == PluginActivityReport::$AM_END && $isSameDate) {
            // begin morning +  end morning
            $arrayRet['begin'] = 'm';
            $arrayRet['end']   = '';
            $arrayRet['txt']   = ' m';
            $arrayRet['lang']  = __('Only on morning', 'activity');

         } else if (strtotime($momentDeb) == strtotime(PluginActivityReport::getAmBegin()) && $momentEnd == PluginActivityReport::$AM_END) {
            // begin morning +  end morning
            $arrayRet['begin'] = '';
            $arrayRet['end']   = 'm';
            $arrayRet['txt']   = '';
            $arrayRet['lang']  = '';

         } else if ($momentDeb == PluginActivityReport::$PM_BEGIN && strtotime($momentEnd) == strtotime(PluginActivityReport::getPmEnd())) {
            // begin afternoon +  end afternoon
            $arrayRet['begin'] = 'a';
            $arrayRet['end']   = '';
            $arrayRet['txt']   = ' a';
            $arrayRet['lang']  = __('Only on afternoon', 'activity');
         }
      }

      return $arrayRet;
   }

   private function getCbsForPeriod($at) {
      $arrayRet   = [];
      $actionTime = $at / PluginActivityReport::getAllDay();

      // default values
      $cbBeginChecked = PluginActivityReport::$ALL_DAY_LABEL;
      $cbEndChecked   = PluginActivityReport::$DEFAULT_LABEL;

      $arrayRet['cbBegin'][PluginActivityReport::$AM_LABEL]['disabled']      = false;
      $arrayRet['cbBegin'][PluginActivityReport::$PM_LABEL]['disabled']      = false;
      $arrayRet['cbBegin'][PluginActivityReport::$ALL_DAY_LABEL]['disabled'] = false;
      $arrayRet['cbEnd'][PluginActivityReport::$AM_LABEL]['disabled']        = true;
      $arrayRet['cbEnd'][PluginActivityReport::$PM_LABEL]['disabled']        = true;
      $arrayRet['cbEnd'][PluginActivityReport::$ALL_DAY_LABEL]['disabled']   = true;

      if ($actionTime > 0) {

         $isSameDate = date('Y-m-d', strtotime($this->fields['begin'])) == date('Y-m-d', strtotime($this->fields['end']));

         $momentDeb = date('H:i:s', strtotime($this->fields['begin']));
         $momentEnd = date('H:i:s', strtotime($this->fields['end']));

         if ($momentDeb == PluginActivityReport::getAmBegin() && $momentEnd == PluginActivityReport::getPmEnd() && !$isSameDate) {
            // begin morning +  end all day +  !same day
            $arrayRet['cbBegin'][PluginActivityReport::$PM_LABEL]['disabled']      = false;
            $arrayRet['cbBegin'][PluginActivityReport::$ALL_DAY_LABEL]['disabled'] = false;
            $arrayRet['cbEnd'][PluginActivityReport::$AM_LABEL]['disabled']        = false;
            $arrayRet['cbEnd'][PluginActivityReport::$ALL_DAY_LABEL]['disabled']   = false;
            $cbBeginChecked                                                        = PluginActivityReport::$ALL_DAY_LABEL;
            $cbEndChecked                                                          = PluginActivityReport::$ALL_DAY_LABEL;

         } else if ($momentDeb == PluginActivityReport::getAmBegin() && $momentEnd == PluginActivityReport::getPmEnd() && $isSameDate && $actionTime != 1) {
            // begin morning +  sameday
            $arrayRet['cbBegin'][PluginActivityReport::$PM_LABEL]['disabled']      = false;
            $arrayRet['cbBegin'][PluginActivityReport::$AM_LABEL]['disabled']      = false;
            $arrayRet['cbBegin'][PluginActivityReport::$ALL_DAY_LABEL]['disabled'] = false;
            $cbBeginChecked                                                        = PluginActivityReport::$AM_LABEL;
            $cbEndChecked                                                          = PluginActivityReport::$DEFAULT_LABEL;

         } else if ($momentDeb == PluginActivityReport::getAmBegin() && $momentEnd == PluginActivityReport::getPmEnd() && $isSameDate && $actionTime == 1) {
            // begin allday +  sameday
            $arrayRet['cbBegin'][PluginActivityReport::$PM_LABEL]['disabled']      = false;
            $arrayRet['cbBegin'][PluginActivityReport::$AM_LABEL]['disabled']      = false;
            $arrayRet['cbBegin'][PluginActivityReport::$ALL_DAY_LABEL]['disabled'] = false;
            $cbBeginChecked                                                        = PluginActivityReport::$ALL_DAY_LABEL;
            $cbEndChecked                                                          = PluginActivityReport::$DEFAULT_LABEL;

         } else if ($momentDeb == PluginActivityReport::$PM_BEGIN && $momentEnd == PluginActivityReport::getPmEnd() && $isSameDate) {
            // begin afternoon  +  sameday
            $arrayRet['cbBegin'][PluginActivityReport::$PM_LABEL]['disabled']      = false;
            $arrayRet['cbBegin'][PluginActivityReport::$AM_LABEL]['disabled']      = false;
            $arrayRet['cbBegin'][PluginActivityReport::$ALL_DAY_LABEL]['disabled'] = false;
            $cbBeginChecked                                                        = PluginActivityReport::$PM_LABEL;
            $cbEndChecked                                                          = PluginActivityReport::$DEFAULT_LABEL;

         } else if ($momentDeb == PluginActivityReport::$PM_BEGIN && $momentEnd == PluginActivityReport::$AM_END) {
            // begin afternoon / end afternoon
            $arrayRet['cbBegin'][PluginActivityReport::$AM_LABEL]['disabled']      = true;
            $arrayRet['cbBegin'][PluginActivityReport::$PM_LABEL]['disabled']      = false;
            $arrayRet['cbBegin'][PluginActivityReport::$ALL_DAY_LABEL]['disabled'] = false;
            $arrayRet['cbEnd'][PluginActivityReport::$AM_LABEL]['disabled']        = false;
            $arrayRet['cbEnd'][PluginActivityReport::$ALL_DAY_LABEL]['disabled']   = false;
            $cbBeginChecked                                                        = PluginActivityReport::$PM_LABEL;
            $cbEndChecked                                                          = PluginActivityReport::$AM_LABEL;

         } else if ($momentDeb == PluginActivityReport::getAmBegin() && $momentEnd == PluginActivityReport::$AM_END) {
            // begin morning +  end morning
            $arrayRet['cbBegin'][PluginActivityReport::$AM_LABEL]['disabled']      = true;
            $arrayRet['cbBegin'][PluginActivityReport::$PM_LABEL]['disabled']      = false;
            $arrayRet['cbBegin'][PluginActivityReport::$ALL_DAY_LABEL]['disabled'] = false;
            $arrayRet['cbEnd'][PluginActivityReport::$AM_LABEL]['disabled']        = false;
            $arrayRet['cbEnd'][PluginActivityReport::$ALL_DAY_LABEL]['disabled']   = false;
            $cbBeginChecked                                                        = PluginActivityReport::$AM_LABEL;
            $cbEndChecked                                                          = PluginActivityReport::$AM_LABEL;

         } else if ($momentDeb == PluginActivityReport::$PM_BEGIN && $momentEnd == PluginActivityReport::getPmEnd()) {
            // begin afternoon +  end afternoon
            $arrayRet['cbBegin'][PluginActivityReport::$PM_LABEL]['disabled']      = false;
            $arrayRet['cbBegin'][PluginActivityReport::$ALL_DAY_LABEL]['disabled'] = false;
            $arrayRet['cbEnd'][PluginActivityReport::$AM_LABEL]['disabled']        = false;
            $arrayRet['cbEnd'][PluginActivityReport::$ALL_DAY_LABEL]['disabled']   = false;
            $cbBeginChecked                                                        = PluginActivityReport::$PM_LABEL;
            $cbEndChecked                                                          = PluginActivityReport::$ALL_DAY_LABEL;
         }
      }

      $arrayRet['cbBegin']['checked'] = $cbBeginChecked;
      $arrayRet['cbEnd']['checked']   = $cbEndChecked;

      if (isset($this->fields['id']) && $this->fields['id'] > 0) {
         $arrayRet['cbBegin'][PluginActivityReport::$AM_LABEL]['disabled']      = true;
         $arrayRet['cbBegin'][PluginActivityReport::$PM_LABEL]['disabled']      = true;
         $arrayRet['cbBegin'][PluginActivityReport::$ALL_DAY_LABEL]['disabled'] = true;
         $arrayRet['cbEnd'][PluginActivityReport::$AM_LABEL]['disabled']        = true;
         $arrayRet['cbEnd'][PluginActivityReport::$PM_LABEL]['disabled']        = true;
         $arrayRet['cbEnd'][PluginActivityReport::$ALL_DAY_LABEL]['disabled']   = true;
      }

      return $arrayRet;
   }


   /**
    * Display the activity form
    *
    * @param $ID integer ID of the item
    * @param $options array
    *     - target filename : where to go when done.
    *     - withtemplate boolean : template or basic item
    *
    * @return boolean item found
    * */
   function showForm($ID, $options = []) {
      global $CFG_GLPI;

      $dbu    = new DbUtils();
      $AllDay = PluginActivityReport::getAllDay();

      $_SESSION['notification_holidayvalidation'] = "false";

      $options['colspan'] = 1;

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      if (!empty($ID)) {
         if ($this->fields["users_id"] != Session::getLoginUserID() && !Session::haveRight("plugin_activity_all_users", 1)) {
            return false;
         }
      }

      $savedValues = $this->getSavedValues();
      if ($savedValues) {
         $this->fields = $savedValues;
         if (!isset($this->fields['id'])) {
            $this->fields['id'] = '';
         }
      }

      if (isset($this->fields['actiontime']) && $this->fields['actiontime'] > 0) {
         $actionTime = $this->fields['actiontime'];
      } else {
         $actionTime = $AllDay;
      }

      $listCbs        = $this->getCbsForPeriod($actionTime);
      $cbBeginChecked = $listCbs['cbBegin']['checked'];
      $cbEndChecked   = $listCbs['cbEnd']['checked'];

      if (isset($options['from_planning_edit_ajax'])
          && $options['from_planning_edit_ajax']) {
         echo Html::hidden('from_planning_edit_ajax');
      }

      echo "<tr class='tab_bg_1'>";
      echo "<td>";

      echo "<table width='100%'>";
      echo "<tr class='tab_bg_2'><td colspan='3'>" . __('Planning') . "</td></tr>";

      if (isset($this->fields["begin"]) && !empty($this->fields["begin"])) {
         $begin = $this->fields["begin"];
         $end   = $this->fields["end"];
      } else {
         $begin = date("Y-m-d") . " " . PluginActivityReport::getAmBegin();
         $end   = date("Y-m-d") . " " . PluginActivityReport::getAmBegin();
      }

      echo "<tr><td>" . PluginActivityHolidayType::getTypeName(1) . "</td><td>";

      if (isset($this->fields['id']) && $this->fields['id'] != 0) {
         $htype = new PluginActivityHolidayType();
         $htype->getFromDB($this->fields["plugin_activity_holidaytypes_id"]);
         echo $htype->fields['name'];
      } else {
         $params = [
            'name'      => "plugin_activity_holidaytypes_id",
            'value'     => $this->fields["plugin_activity_holidaytypes_id"],
            'on_change' => "plugin_activity_show_periods(\"" . $CFG_GLPI['root_doc'] . "\", this.value);",
            'comments'  => 1];
         Dropdown::show('PluginActivityHolidayType', $params);
      }

      echo "</td></tr>";

      $visibility = 'display: none';
      if ($ID) {
         $holidaytype = new PluginActivityHolidayType();
         $holidaytype->getFromDB($this->fields["plugin_activity_holidaytypes_id"]);
         if ($holidaytype->fields['is_period']) {
            $visibility = '';
         }
      }

      echo "<tr id='tr_plugin_activity_holidayperiods_id' style='$visibility'>";
      echo "<td>" . PluginActivityHolidayPeriod::getTypeName(1) . "</td><td>";

      $params = [
         'name'     => "plugin_activity_holidayperiods_id",
         'value'    => $this->fields["plugin_activity_holidayperiods_id"],
         'comments' => 1];

      if (empty($ID)) {
         $params['condition'] = ['archived' => 0];
         $params['on_change'] = "plugin_activity_show_details(\"" . $CFG_GLPI['root_doc'] . "\", this.value);";
      }

      Dropdown::show('PluginActivityHolidayPeriod', $params);

      echo "</td></tr>";

      // ------------------------------------------------------------------------
      // begin
      // ------------------------------------------------------------------------
      echo "<tr><td>" . __('Start date') . "</td><td>";
      echo "<input type='text' name='begin' id='begin' size='12' ";
      if (!isset($this->fields['id']) || $this->fields['id'] == '') {
         $root_doc = json_encode($CFG_GLPI['root_doc']);
         echo " onchange='updateDuration(this, $root_doc);' ";
         echo "value='" . date('d-m-Y', strtotime($begin)) . "' />";
         echo "<input type='hidden' name='is_planned' value='1' />";
         echo "<input type='hidden' name='actiontime' id='actiontime' value='" . ($actionTime / $AllDay) . "' />";
         $this->initDate('begin');
      } else {
         echo " disabled='disabled' ";
         echo "value='" . date('d-m-Y', strtotime($begin)) . "' />";
      }
      echo "</td></tr>";

      if ($ID <= 0) {
         echo "<tr><td>&nbsp;</td><td>";

         $params = [
            'value'    => PluginActivityReport::$AM_LABEL,
            'name'     => 'radio_cb_begindate',
            'cb_id'    => 'cb_begindate_am',
            'checked'  => $cbBeginChecked == PluginActivityReport::$AM_LABEL,
            'disabled' => $listCbs['cbBegin'][PluginActivityReport::$AM_LABEL]['disabled'],
            'title'    => __('Only on morning', 'activity')
         ];
         $this->showCbPeriod($params);

         $params = [
            'value'    => PluginActivityReport::$PM_LABEL,
            'name'     => 'radio_cb_begindate',
            'cb_id'    => 'cb_begindate_pm',
            'checked'  => $cbBeginChecked == PluginActivityReport::$PM_LABEL,
            'disabled' => $listCbs['cbBegin'][PluginActivityReport::$PM_LABEL]['disabled'],
            'title'    => __('Only on afternoon', 'activity')
         ];
         $this->showCbPeriod($params);

         echo "<br/>";
         $params = [
            'value'    => PluginActivityReport::$ALL_DAY_LABEL,
            'name'     => 'radio_cb_begindate',
            'cb_id'    => 'cb_begindate_allday',
            'checked'  => $cbBeginChecked == PluginActivityReport::$ALL_DAY_LABEL,
            'disabled' => $listCbs['cbBegin'][PluginActivityReport::$ALL_DAY_LABEL]['disabled'],
            'title'    => __('All day', 'activity')
         ];
         $this->showCbPeriod($params);

         echo "</td>";
         echo "</tr>";
      }

      // ------------------------------------------------------------------------
      // END DATE
      // ------------------------------------------------------------------------
      echo "<tr><td>" . __('End date') . "</td><td>";
      echo "<input type='text' name='end' id='end' size='12' ";
      if (!isset($this->fields['id']) || $this->fields['id'] == '') {
         $root_doc = json_encode($CFG_GLPI['root_doc']);
         echo " onchange='updateDuration(this, $root_doc);' ";
         echo " value='" . date('d-m-Y', strtotime($end)) . "' />";
         $this->initDate('end');
      } else {
         echo " disabled='disabled' ";
         echo " value='" . date('d-m-Y', strtotime($end)) . "' />";
      }

      echo "</td></tr>";
      if ($ID <= 0) {
         echo "<tr><td>&nbsp;</td><td>";

         $params = [
            'value'    => PluginActivityReport::$AM_LABEL,
            'name'     => 'radio_cb_enddate',
            'cb_id'    => 'cb_enddate_am',
            'checked'  => $cbEndChecked == PluginActivityReport::$AM_LABEL,
            'disabled' => $listCbs['cbEnd'][PluginActivityReport::$AM_LABEL]['disabled'],
            'title'    => __('Only on morning', 'activity')
         ];
         $this->showCbPeriod($params);

         $params = [
            'value'    => PluginActivityReport::$PM_LABEL,
            'name'     => 'radio_cb_enddate',
            'cb_id'    => 'cb_enddate_pm',
            'checked'  => $cbEndChecked == PluginActivityReport::$PM_LABEL,
            'disabled' => $listCbs['cbEnd'][PluginActivityReport::$PM_LABEL]['disabled'],
            'title'    => __('Only on afternoon', 'activity')
         ];
         $this->showCbPeriod($params);

         echo "<br/>";
         $params = [
            'value'    => PluginActivityReport::$ALL_DAY_LABEL,
            'name'     => 'radio_cb_enddate',
            'cb_id'    => 'cb_enddate_allday',
            'checked'  => $cbEndChecked == PluginActivityReport::$ALL_DAY_LABEL,
            'disabled' => $listCbs['cbEnd'][PluginActivityReport::$ALL_DAY_LABEL]['disabled'],
            'title'    => __('All day', 'activity')
         ];
         $this->showCbPeriod($params);

         echo "</td>";
         echo "</tr>";
      }

      // ------------------------------------------------------------------------
      // DURATION
      // ------------------------------------------------------------------------

      echo "<tr>";
      echo "<td>" . __('Duration') . "</td><td>";

      $params['value'] = $this->fields["actiontime"];


      echo "<div id='div_duration'>" . PluginActivityReport::TotalTpsPassesArrondis($actionTime / $AllDay) . "</div>";
      $period = $this->getPeriodForTemplate($actionTime);

      echo $period["lang"];

      echo "</td></tr>";

      echo "</table>";

      echo "</td>";

      echo "<td align='left'  valign='top'>";

      echo "<table width='100%'>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>";
      echo _n('User', 'Users', 1);
      echo "</td>";
      echo "</tr>";
      echo "<tr>";

      echo "<td>";

      if (empty($ID) && Session::haveRight("plugin_activity_all_users", 1)) {
         User::dropdown(['name'      => "users_id",
                         'value'     => Session::getLoginUserID(),
                         'right'     => "interface",
                         'comment'   => 1,
                         'on_change' => "plugin_activity_show_details_users(\"" . $CFG_GLPI['root_doc'] . "\", this.value);"
                        ]);
      } else if (empty($ID) && !Session::haveRight("plugin_activity_all_users", 1)) {
         echo $dbu->getUserName(Session::getLoginUserID());
         echo "<input type='hidden' name='users_id' value='" . Session::getLoginUserID() . "'>";
      }

      if (!empty($ID) && Session::haveRight("plugin_activity_all_users", 1)) {

         if (!(isset($this->fields['id'])) || $this->fields['id'] == '') {
            User::dropdown(['name'    => "users_id",
                            'value'   => $this->fields["users_id"],
                            'right'   => "interface",
                            'comment' => 1
                           ]);
         } else {
            $user = new User();
            $user->getFromDB($this->fields['users_id']);
            echo $user->getName();
         }
      } else if (!empty($ID) && !Session::haveRight("plugin_activity_all_users", 1)) {
         echo $dbu->getUserName($this->fields["users_id"]);
         echo "<input type='hidden' name='users_id' value='" . $this->fields["users_id"] . "'>";
      }

      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>" . __('Description') . "</td>";
      echo "</tr>";
      echo "<tr>";
      echo "<td><textarea cols='75' rows='7' ";
      if (isset($this->fields['id']) && $this->fields['id'] != '') {
         echo " disabled='disabled' ";
      }
      echo "name='comment' >" . $this->fields["comment"] . "</textarea>";
      echo "</td>";
      echo "</tr>";
      echo "</table>";

      echo "</td>";
      echo "</tr>";

      if ($ID) {

         if (($this->fields["users_id"] != Session::getLoginUserID()
              && $this->checkUserIsManager($this->fields["users_id"])
              && Session::haveRight("plugin_activity_can_requestholiday", 1))) {

            echo "<tr class='tab_bg_2'>";
            echo "<td class='right' colspan='4' >\n";
            echo "<input type='submit' name='delete' value=\"" . _sx('button',
                                                                     'Delete permanently') . "\"
                         class='submit' " .
                 Html::addConfirmationOnAction(__('Confirm the final deletion?')) . ">";
            echo "</td></tr>";
            echo "<input type='hidden' name='id' value='" . $this->fields['id'] . "' >";
         } else if ($this->fields["users_id"] == Session::getLoginUserID()) {
            $this->showFormButtons($options);
         }

         echo "</table></div>";
         Html::closeForm();

      } else {
         if ($this->checkUserHasManager()) {
            $this->showFormButtons($options);
         }
      }

      return true;
   }


   function showLinks() {

      if (Session::haveRight("plugin_activity_can_validate", 1)
          && isset($this->fields['id'])
          && $this->fields['id'] > 0) {
         if ($this->fields['global_validation'] == PluginActivityCommonValidation::ACCEPTED) {
            $user = new User();
            $user->getFromDB($this->fields['users_id']);

            if (isset($user->fields['registration_number'])
                && $user->fields['registration_number'] != "") {
               $this->showLinkTXTFile($this->fields['id']);
            } else {
               $this->showErrorRegistrationNumber();
            }
         }
      }

      $this->initJQDatepicker();
   }

   /**
    * Show a checkbox on view
    *
    * @param $params :
    *    value    : value of the checkbox
    *    cb_id    : id of the checkbox
    *    name     : name of the checkbox
    *    checked  : true for a checked checkbox
    *    disabled : true for a disabled checkbox
    *    title    : text to show for the  checkbox
    *
    * Optional :
    *    onclick  : onclick event on the checkbox
    */
   private function showCbPeriod($params) {
      global $CFG_GLPI;

      $root_doc = json_encode($CFG_GLPI['root_doc']);

      if (isset($params['onclick'])) {
         $onclick = $params['onclick'];
      } else {
         $onclick = "updateDuration(this, $root_doc);";
      }

      echo "<label for='" . $params['cb_id'] . "'>";
      echo "   <input type='radio' name='" . $params['name'] . "' id='" . $params['cb_id'] . "' ";
      //         echo "      onclick='updateRadioBtnDate(this);'";
      echo "      value='" . $params['value'] . "'" . ($params['checked'] == true ? " checked='checked' " : '');
      echo "      " . ($params['disabled'] == true ? " disabled='disabled' " : '');
      echo "      onclick='" . $onclick . "'";
      echo "   />";
      echo "   &nbsp" . $params['title'];
      echo "</label>&nbsp&nbsp;";
   }


   private function showErrorRegistrationNumber() {
      global $CFG_GLPI;

      $user = new User();
      $dbu  = new DbUtils();
      $user->getFromDB($this->fields['users_id']);
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>\n";
      echo "<i class='fas fa-info-circle fa-1x'></i>&nbsp;" . __("You must fill a registration number for this user before generating the TXT file.", "activity");
      echo "<br/>";
      echo __("See this page", "activity") . " - <a href='" . $user->getLinkURL() . "' target='_blank'>" . $dbu->getUserName($user->fields['id']) . " </a>";
      echo "</td></tr></table>";
   }


   private function getBodyMail($dateComplete, $date, $userName, $approverFullName) {

      $rows = file(GLPI_ROOT . '/plugins/activity/files/templates/mail_template.txt');

      $finalRows = '';

      foreach ($rows as $row => $data) {

         //get row data
         if (strpos($data, "{{holiday_date_complete}}") !== false) {
            $finalRows .= str_ireplace("{{holiday_date_complete}}", $dateComplete, $data);

         } else if (strpos($data, "{{approver_full_name}}") !== false) {
            $finalRows .= str_ireplace("{{approver_full_name}}", $approverFullName, $data);

         } else if (strpos($data, "{{user_name}}") !== false) {
            $finalRows .= str_ireplace("{{user_name}}", $userName, $data);

         } else if (strpos($data, "{{holiday_date}}") !== false) {
            $finalRows .= str_ireplace("{{holiday_date}}", date("d/m/y"), $data);

         } else {
            $finalRows .= $data;
         }
         $finalRows .= "%0D%0A";
      }

      return $finalRows;
   }


   public function showLinkTXTFile($holidaysId) {
      global $CFG_GLPI;

      $holiday = new PluginActivityHoliday();
      $holiday->getFromDB($holidaysId);
      $user = new User();
      $user->getFromDB($holiday->fields['users_id']);

      $period           = $this->getPeriodForTemplate($holiday->fields['actiontime']);
      $dateBegin        = date('d/m/Y', strtotime($holiday->fields['begin'])) . " " . $period['begin'];
      $userName         = (isset($user->fields['firstname']) ? ucfirst($user->fields['firstname']) : '') . " " . (isset($user->fields['realname']) ? strtoupper($user->fields['realname']) : '');
      $approverFullname = (isset($_SESSION['glpifirstname']) ? ucfirst($_SESSION['glpifirstname']) : '') . " " . (isset($_SESSION['glpirealname']) ? strtoupper($_SESSION['glpirealname']) : '');

      $url = $CFG_GLPI['root_doc'] . "/plugins/activity/front/generateTXTFile.php?holidays_id=$holidaysId";

      if (PluginActivityHolidayValidation::canValidate($holidaysId)) {
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1'>";
         echo "<td>\n";

         echo "<ul style='list-style: none' >";
         echo "<li style='margin-left:16px'><a href='$url' target='_blank' style='cursor:pointer;'>";
         echo "<i class='far fa-file-alt fa-2x'></i>&nbsp;" . __('Generate TXT file for this holiday', 'activity');
         echo "</a></li>";

         $used_mail_for_holidays = "";
         $opt                    = new PluginActivityOption();
         $opt->getFromDB(1);
         if ($opt) {
            $used_mail_for_holidays = $opt->fields['used_mail_for_holidays'];
         }
         if (!empty($used_mail_for_holidays)) {
            echo "<li style='margin-left:16px;'><a href='mailto:" . $used_mail_for_holidays;
            echo "&Subject=" . __("Holiday request from", "activity") . " " . $userName . " " . __("of", "activity") . " " . $dateBegin;
            echo "&Body=" . $this->getBodyMail($dateBegin, date("d/m/Y", strtotime($holiday->fields['begin'])), $userName, $approverFullname);

            echo "'>";
            echo "<i class='far fa-envelope fa-2x'></i>&nbsp;" . __('Generate mail for this holiday', 'activity');
            echo "</a></li>";
         }
         echo "</ul>";
         echo "</td></tr></table>";
      }
   }


   /**
    * This function allows to show javascript headers, needed to javascript and ajax purpose.
    */
   public function showHeaderJS() {
      echo "\n<script type='text/javascript'>\n";
   }

   /**
    * This function close a javascript block (used after showHeaderJs() ).
    */
   public function closeFormJS() {
      echo "</script>\n";
   }


   static function getAlreadyPlannedInformation($val) {
      global $CFG_GLPI;

      $out = "";

      $out .= self::getTypeName() . ' : ' . Html::convDateTime($val["begin"]) . ' -> ' .
              Html::convDateTime($val["end"]) . ' : ';
      $out .= "<a href='" . $CFG_GLPI["root_doc"] . "/plugins/activity/front/holiday.form.php?id=" .
              $val["id"] . "'>";
      $out .= Html::resume_text($val["name"], 80) . '</a>';

      return $out;
   }

   /**
    * Add items in the items fields of the parm array
    * Items need to have an unique index beginning by the begin date of the item to display
    * needed to be correcly displayed
    **/
   static function populatePlanning($options = []) {
      global $DB, $CFG_GLPI;

      $default_options = [
         'color'               => '',
         'event_type_color'    => '',
         'check_planned'       => false,
         'display_done_events' => true,
      ];
      $options         = array_merge($default_options, $options);

      $interv  = [];
      $holiday = new self;

      if (!isset($options['begin']) || ($options['begin'] == 'NULL')
          || !isset($options['end']) || ($options['end'] == 'NULL')) {
         return $interv;
      }

      if (!$options['display_done_events']) {
         return $interv;
      }

      $who       = $options['who'];
      $who_group = $options['whogroup'];
      $begin     = $options['begin'];
      $end       = $options['end'];

      // Get items to print
      $ASSIGN = "";

      //      if ($who_group === "mine") {
      //         if (count($_SESSION["glpigroups"])) {
      //            $groups = implode("','", $_SESSION['glpigroups']);
      //            $ASSIGN = "`users_id`
      //                           IN (SELECT DISTINCT `users_id`
      //                               FROM `glpi_groups_users`
      //                               WHERE `glpi_groups_users`.`groups_id` IN ('$groups'))
      //                                     AND ";
      //         } else { // Only personal ones
      //            $ASSIGN = "`users_id` = '$who'
      //                       AND ";
      //         }
      //      } else {
      if ($who > 0) {
         $ASSIGN = "`users_id` = '$who'
                       AND ";
      }
      if ($who_group > 0) {
         $ASSIGN = "`users_id` IN (SELECT `users_id`
                                     FROM `glpi_groups_users`
                                     WHERE `groups_id` = '$who_group')
                                           AND ";
      }
//      if ($who_group > 0) {
//         $ASSIGN = "`groups_id` = '$who_group'
//                       AND ";
//      }
      //      }

      $query = " SELECT `glpi_plugin_activity_holidays`.`id`,
                              `glpi_plugin_activity_holidays`.`name`,
                              `glpi_plugin_activity_holidays`.`global_validation` AS global_validation,
                              `begin`,
                              `end`,
                              `actiontime`,
                              `users_id`,
                              `glpi_plugin_activity_holidaytypes`.`name` AS type,
                              `glpi_plugin_activity_holidays`.`comment`";
      $query .= " FROM `glpi_plugin_activity_holidays` ";
      $query .= "LEFT JOIN `glpi_plugin_activity_holidaytypes`
                      ON (`glpi_plugin_activity_holidaytypes`.`id` = `glpi_plugin_activity_holidays`.`plugin_activity_holidaytypes_id`) ";
      $query .= " WHERE ";
      $query .= " $ASSIGN ";
      $query .= " `is_planned`= 1 ";
      $query .= " AND `glpi_plugin_activity_holidays`.`global_validation`= " . PluginActivityCommonValidation::ACCEPTED;

      //$query.= getEntitiesRestrictRequest("AND","glpi_plugin_activity_holidays", '',
      //                                       $_SESSION["glpiactiveentities"],false);

      $query .= " AND '$begin' < `end` AND '$end' > `begin`
                  ORDER BY `begin` ";

      $result = $DB->query($query);

      if ($DB->numrows($result) > 0) {

         for ($i = 0; $data = $DB->fetchArray($result); $i++) {

            $key                              = $data["begin"] . "$$" . "PluginActivityHoliday" . $data["id"];
            $interv[$key]['color']            = $options['color'];
            $interv[$key]['event_type_color'] = $options['event_type_color'];
            $interv[$key]["itemtype"]         = 'PluginActivityHoliday';
            $interv[$key]["id"]               = $data["id"];
            $interv[$key]["users_id"]         = $data["users_id"];
            $interv[$key]["actiontime"]       = $data["actiontime"];
            if (strcmp($begin, $data["begin"]) > 0) {
               $interv[$key]["begin"] = $begin;
            } else {
               $interv[$key]["begin"] = $data["begin"];
            }
            if (strcmp($end, $data["end"]) < 0) {
               $interv[$key]["end"] = $end;
            } else {
               $interv[$key]["end"] = $data["end"];
            }
            $interv[$key]["name"]    = Html::resume_text($data["name"], $CFG_GLPI["cut"]);
            $interv[$key]["type"]    = $data["type"];
            $interv[$key]["status"]  = $data["global_validation"];
            $interv[$key]["content"] = Html::resume_text($data["comment"],
                                                         $CFG_GLPI["cut"]);
            $interv[$key]["url"]     = $CFG_GLPI["root_doc"] . "/plugins/activity/front/holiday.form.php?id=" .
                                       $data['id'];
            $interv[$key]["ajaxurl"] = $CFG_GLPI["root_doc"] . "/ajax/planning.php" .
                                       "?action=edit_event_form" .
                                       "&itemtype=PluginActivityHoliday" .
                                       "&id=" . $data['id'] .
                                       "&url=" . $interv[$key]["url"];

            $holiday->getFromDB($data["id"]);
            $interv[$key]["editable"] = $holiday->canUpdateItem();

            //delete of weekend days
            if ($holiday->countWe($interv[$key]["begin"], $interv[$key]["end"]) != 0) {
               $holiday->excludeWe($interv[$key]["begin"], $interv[$key]["end"], $interv, $key, $data['id']);
            }
         }
      }
      return $interv;

   }


   /**
    * Display a Planning Item
    *
    * @param $parm Array of the item to display
    *
    * @return Nothing (display function)
    **/
   static function displayPlanningItem(array $val, $who, $type = "", $complete = 0) {

      $rand = mt_rand();
      $dbu  = new DbUtils();
      $html = "";

      if ($complete) {

         if ($val["end"]) {
            $html .= "<strong>" . __('End date') . "</strong> : " . Html::convdatetime($val["end"]) . "<br>";
         }
         if ($val["users_id"] && $who != 0) {
            $html .= "<strong>" . __('User') . "</strong> : " . $dbu->getUserName($val["users_id"]) . "<br>";
         }
         if ($val["type"]) {
            $html .= "<strong>" . PluginActivityHolidayType::getTypeName(1) . "</strong> : " .
                     $val["type"] . "<br>";
         }
         if ($val["content"]) {
            $html .= "<strong>" . __('Description') . "</strong> : " . $val["content"];
         }
      } else {

         if ($val["actiontime"]) {
            $html .= "<strong>" . __('Total duration') . "</strong> : " . Html::timestampToString($val['actiontime'], false) . "<br>";
         }

         $html .= "<div class='event-description'>" . $val["content"] . "</div>";
         $html .= Html::showToolTip($val["content"],
                                    ['applyto' => "activity_" . $val["id"] . $rand,
                                     'display' => false]);

      }

      return $html;
   }


   static function queryUserHolidays($criteria) {

      $query = "SELECT `glpi_plugin_activity_holidays`.`name` AS name,
                             `glpi_plugin_activity_holidays`.`id` AS id,
                             `glpi_plugin_activity_holidays`.`actiontime` AS actiontime,
                             `glpi_plugin_activity_holidays`.`comment` AS comment,
                             `glpi_plugin_activity_holidaytypes`.`name` AS type,
                             `glpi_plugin_activity_holidaytypes`.`is_holiday` AS is_holiday,
                             `glpi_plugin_activity_holidaytypes`.`is_sickness` AS is_sickness,
                             `glpi_plugin_activity_holidaytypes`.`is_part_time` AS is_part_time,
                             `glpi_plugin_activity_holidays`.`allDay`,
                             `glpi_plugin_activity_holidays`.`begin` AS begin,
                             `glpi_plugin_activity_holidays`.`end` AS end,
                             `glpi_plugin_activity_holidays`.`global_validation` AS global_validation,
                             `glpi_plugin_activity_holidays`.`plugin_activity_holidaytypes_id` AS type_id

                     FROM `glpi_plugin_activity_holidays` ";//,`glpi_entities`.`name` AS entity
      $query .= " LEFT JOIN `glpi_users`
                           ON (`glpi_users`.`id` = `glpi_plugin_activity_holidays`.`users_id`)";
      $query .= " LEFT JOIN `glpi_plugin_activity_holidaytypes`
                           ON (`glpi_plugin_activity_holidaytypes`.`id` = `glpi_plugin_activity_holidays`.`plugin_activity_holidaytypes_id`)";
      $query .= " WHERE ";
      $query .= "  `glpi_plugin_activity_holidays`.`users_id` = '" . $criteria["users_id"] . "' ";

      if (isset($criteria['begin'])) {
         $query .= " AND (`glpi_plugin_activity_holidays`.`begin` >= '" . $criteria["begin"] . "'
                        AND `glpi_plugin_activity_holidays`.`begin` <= '" . $criteria["end"] . "') ";
      }
      if (isset($criteria['global_validation'])) {
         $query .= "AND `global_validation` = " . $criteria['global_validation'];
      }
      //.getEntitiesRestrictRequest("AND", "glpi_plugin_activity_holidays")."
      $query .= " AND `glpi_plugin_activity_holidays`.`actiontime` != 0";
      $query .= " ORDER BY `glpi_plugin_activity_holidays`.`name`";

      return $query;
   }


   static function isUserInHoliday($date, $users = []) {
      global $DB;

      $in_holiday = [];

      $query = "SELECT `glpi_plugin_activity_holidays`.`name` AS name,
                             `glpi_plugin_activity_holidays`.`id` AS id,
                             `glpi_users`.`realname`,
                             `glpi_users`.`name`,
                             `glpi_users`.`firstname`
                      FROM `glpi_plugin_activity_holidays`
                      LEFT JOIN `glpi_users`
                         ON (`glpi_users`.`id` = `glpi_plugin_activity_holidays`.`users_id`)
                      WHERE `glpi_plugin_activity_holidays`.`users_id` IN ('" . implode("','", $users) . "')
                      AND `glpi_plugin_activity_holidays`.`end` >= '" . $date . "'
                      AND `glpi_plugin_activity_holidays`.`begin` <= '" . $date . "'";

      $query .= " AND `global_validation`= " . PluginActivityCommonValidation::ACCEPTED;
      $query .= " ORDER BY `glpi_plugin_activity_holidays`.`name`";

      //"
      //                .getEntitiesRestrictRequest("AND", "glpi_plugin_activity_holidays")."
      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetchArray($result)) {
            $in_holiday[] = $data['realname'] . ' ' . $data['firstname'];
         }
         return $in_holiday;
      }

      return false;
   }


   function isWeekend($date, $begin = true) {
      if (date('H:i:s', strtotime($date)) == "00:00:00") {
         if ($begin) {
            $date = date("Y-m-d H:i:s", strtotime("+1 minutes", strtotime($date)));
         } else {
            $date = date("Y-m-d H:i:s", strtotime("-1 minutes", strtotime($date)));
         }
      }

      return (date('N', strtotime($date)) >= 6);
   }


   static function userHasPlannedHolidays($dateBegin, $dateEnd, $userId) {
      $holiday = new PluginActivityHoliday();

      $condition = [
         "OR"         => [["AND" => ["`glpi_plugin_activity_holidays`.`begin`" => ["<=", $dateBegin],
                                     "`glpi_plugin_activity_holidays`.`end`"   => [">=", $dateBegin]]],
                          ["AND" => ["`glpi_plugin_activity_holidays`.`begin`" => ["<=", $dateEnd],
                                     "`glpi_plugin_activity_holidays`.`end`"   => [">=", $dateEnd]]]
         ],
         "`users_id`" => $userId
      ];
      $dbu       = new DbUtils();
      $datas     = $dbu->getAllDataFromTable($holiday->getTable(), $condition);

      if (sizeof($datas) > 0) {
         return true;
      } else {
         return false;
      }
   }

   static function checkInHolidays($input, $holidays = []) {
      if (isset($input['begin'])) {
         $begin = date('Y-m-d', strtotime($input['begin']));

         if (!empty($holidays)) {
            foreach ($holidays as $holiday) {
               $hbegin = $holiday['begin'];
               $hend   = $holiday['end'];

               if ($holiday['is_perpetual'] == 1) {
                  $hb = date('m-d', strtotime($holiday['begin']));
                  $he = date('m-d', strtotime($holiday['end']));
                  $hd = date('m-d', strtotime($begin));

                  if ($hd >= $hb
                      && $hd <= $he) {
                     return true;
                  }

               } else {

                  if ($begin >= $hbegin
                      && $begin <= $hend) {
                     return true;
                  }
               }
            }
         }
      }
      return false;
   }

   static function getCalendarHolidaysArray($entities_id) {
      global $DB;

      $holidays = [];

      $calendars_id = Entity::getUsedConfig('calendars_id', $entities_id);
      if ($calendars_id > 0) {

         $query = "SELECT `glpi_holidays`.*
                      FROM `glpi_calendars_holidays`
                      INNER JOIN `glpi_holidays`
                           ON (`glpi_calendars_holidays`.`holidays_id` = `glpi_holidays`.`id`)
                      WHERE `glpi_calendars_holidays`.`calendars_id` = '" . $calendars_id . "'";

         if ($result = $DB->query($query)) {
            while ($data = $DB->fetchArray($result)) {
               $holidays[] = ['begin'        => $data['begin_date'],
                              'end'          => $data['end_date'],
                              'is_perpetual' => $data['is_perpetual']];
            }
         }
      }
      return $holidays;
   }


   /**
    * Get the number of days that are in week end
    *
    * @param type $debut
    * @param type $fin
    * @param type $holidays
    *
    * @return int
    */
   function countWe($debut, $fin, $holidays = []) {
      $start = strtotime($debut);
      $end   = strtotime($fin);

      $joursem = ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'];
      $We      = ['Di', 'Sa'];
      $countwe = 0;

      for ($i = $start; $i <= $end; $i = $i + 86400) {
         $jour           = date("w", $i); // 2010-05-01, 2010-05-02, etc
         $input['begin'] = date('Y-m-d', $i);

         $isholiday = PluginActivityHoliday::checkInHolidays($input, $holidays);

         if (in_array($joursem[$jour], $We) || $isholiday == 1) {
            $countwe++;
         }
      }

      return $countwe;
   }

   /**
    * Delete week end days
    *
    * @param type $debut
    * @param type $fin
    * @param type $interv
    * @param type $key
    *
    * @return type
    */
   function excludeWe($debut, $fin, &$interv, $key, $id) {
      $start = strtotime($debut);
      $end   = strtotime($fin);

      $joursem = ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'];
      $We      = ['Di', 'Sa'];

      $begin   = true;
      $new_key = $key;

      for ($i = $start; $i <= $end; $i = $i + 86400) {
         $jour           = date("w", $i); // 2010-05-01, 2010-05-02, etc
         $input['begin'] = date('Y-m-d', $i);

         $isholiday = PluginActivityHoliday::checkInHolidays($input);

         if (in_array($joursem[$jour], $We) || $isholiday) {
            if ($begin) {
               $interv[$new_key]['end'] = date('Y-m-d', ($i - 86400)) . ' ' . PluginActivityReport::getPmEnd();
               $begin                   = false;
            }
         } else {
            if (!$begin) {
               $data_begin                = date('Y-m-d', $i) . ' ' . PluginActivityReport::getAmBegin();
               $new_key                   = $data_begin . "$$" . "PluginActivityHoliday$id";
               $interv[$new_key]          = $interv[$key];
               $interv[$new_key]['begin'] = $data_begin;
               $interv[$new_key]['end']   = $fin;
               $begin                     = true;
            }
         }
      }
   }

   /**
    * This function allows to create a JQuery datepicker
    *
    * @param String $id : id of the input text to be transform as a datepicker
    */
   public function initDate($id) {
      global $CFG_GLPI;
      $this->showHeaderJS();
      echo "$(document).ready(function() {
               $('#" . $id . "').datepicker({
                       showOn: 'both',
                       buttonText: '<i class=\"far fa-calendar-alt\"></i>',
                       width : 2,
                       height : 2,
                       dateFormat: 'dd-mm-yy',
                       firstDay: 1,
                       beforeShowDay: $.datepicker.noWeekends,
                       constrainInput: true,
                   });

                   $('.ui-datepicker-trigger').mouseover(function() {
                      $(this).css('cursor', 'pointer');
                   });
               });";
      $this->closeFormJS();
   }


   function initJQDatepicker() {

      $langMonth = array_values(Toolbox::getMonthsOfYearArray());
      $langDays  = array_values(Toolbox::getDaysOfWeekArray());
      foreach ($langMonth as $month) {
         $langMonthShort[] = substr($month, 0, 4);
      }
      foreach ($langDays as $day) {
         $langDaysShort[] = substr($day, 0, 3);
      }
      foreach ($langDays as $day) {
         $langDaysMin[] = substr($day, 0, 2);
      }

      $this->showHeaderJS();
      echo "    $.datepicker.regional['fr'] = {clearText: '" . __('Clear') . "', clearStatus: '',\n";
      echo "        closeText: '" . __('Close') . "', closeStatus: '" . __('Close without clearing', 'activity') . "',\n";
      echo "        prevText: '< " . __('Previous') . "', prevStatus: '" . __('See previous month', 'activity') . "',\n";
      echo "        nextText: '" . __('Next') . " >', nextStatus: '" . __('See next month', 'activity') . "',\n";
      echo "        currentText: 'Courant', currentStatus: '" . __('See current month', 'activity') . "',\n";
      echo "        monthNames: " . json_encode($langMonth) . ",\n";
      echo "        monthNamesShort: " . json_encode($langMonthShort) . ",\n";
      echo "        monthStatus: '" . __('See another month', 'activity') . "', yearStatus: '" . __('See another year', 'activity') . "',\n";
      echo "        weekHeader: 'Sm', weekStatus: '',\n";
      echo "        dayNames: " . json_encode($langDays) . ",\n";
      echo "        dayNamesShort: " . json_encode($langDaysShort) . ",\n";
      echo "        dayNamesMin: " . json_encode($langDaysMin) . ",\n";
      echo "        dayStatus: '" . __('Use DD as first day of week', 'activity') . "', dateStatus: '" . __('Choose the DD, MM d', 'activity') . "',\n";
      echo "        dateFormat: 'dd/mm/yy', firstDay: 0,\n";
      echo "        initStatus: '" . __('Choose the date', 'activity') . "', isRTL: false};\n";
      echo "    $.datepicker.setDefaults($.datepicker.regional['fr']);\n";

      $this->closeFormJS();
   }


   /**
    * Get the specific massive actions
    *
    * @param $checkitem link item to check right   (default NULL)
    *
    * @return an array of massive actions
    **@since version 0.84
    *
    */
   function getSpecificMassiveActions($checkitem = null) {
      $isadmin = $this->canCreate();
      $actions = parent::getSpecificMassiveActions($checkitem);

      if ($isadmin) {
         $actions['PluginActivityHoliday' . MassiveAction::CLASS_ACTION_SEPARATOR . 'updateAllValidations'] = _n('Approve holiday', 'Approve holidays', 2, 'activity');
      }

      return $actions;
   }

   static function showMassiveActionsSubForm(MassiveAction $ma) {

      switch ($ma->getAction()) {
         case "updateAllValidations" :
            $options['name']    = _n('Status', 'Statuses', 1);
            $options['field']   = 'global_validation';
            $options['display'] = true;
            $options['value']   = PluginActivityCommonValidation::WAITING;
            $options['id']      = '';

            echo "<table class='tab_cadre'>";
            echo "   <tr class='tab_bg_1'>";
            echo "      <td >";
            echo __('Approval');
            echo "      </td>";

            echo "      <td >";
            PluginActivityCommonValidation::dropdownStatus($options['field'], $options);
            echo "      </td>";
            echo "   </tr>";

            echo "   <tr class='tab_bg_1'>";
            echo "      <td >" . _n('Comment', 'Comments', 1, 'activity') . "</td>";
            echo "      <td ><textarea name='comment_validation' id='comment_validation' cols='30' rows='8'></textarea></td>";
            echo "   </tr>";
            echo "</table>";
            break;
      }

      return parent::showMassiveActionsSubForm($ma);
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
    * */
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {

      $input = $ma->getInput();

      switch ($ma->getAction()) {
         case "updateAllValidations":
            if (Session::haveRight('plugin_activity_can_validate', 1)) {
               $holidayValidation = new PluginActivityHolidayValidation();
               $dbu               = new DbUtils();
               foreach ($ids as $key => $val) {
                  $condition = ["plugin_activity_holidays_id" => $key, "users_id_validate" => Session::getLoginUserID()];
                  $datas     = $dbu->getAllDataFromTable($holidayValidation->getTable(), $condition);

                  foreach ($datas as $data) {
                     $input['id']     = $data['id'];
                     $input['status'] = $input['global_validation'];
                     if ($holidayValidation->update($input)) {
                        $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                     } else {
                        $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                     }
                  }
               }
            } else {
               //TODO voir quoi mettre à la place de $key
               $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_NORIGHT);
            }

            break;

         default :
            return parent::doSpecificMassiveActions($input);
      }
   }

   function getForbiddenStandardMassiveAction() {

      $forbidden = parent::getForbiddenStandardMassiveAction();

      $forbidden[] = 'update';

      return $forbidden;
   }

   /**
    *
    * @param type  $users_id
    * @param type  $periods
    *
    * @return type
    * @global type $DB
    *
    */
   function countNbHolidayByPeriod($users_id, $periods) {
      global $DB;

      $nb_jours['total'] = 0;
      $AllDay            = PluginActivityReport::getAllDay();

      foreach ($periods as $period) {
         $nb_jours['period'][$period['id']] = 0;

         $query = "SELECT `glpi_plugin_activity_holidays`.`begin` AS begin, "
                  . "  `glpi_plugin_activity_holidays`.`end` AS end, "
                  . "  `glpi_plugin_activity_holidays`.`actiontime` AS actiontime, "
                  . "  `glpi_plugin_activity_holidaytypes`.`id` AS types_id, "
                  . "  `glpi_plugin_activity_holidaytypes`.`name`, "
                  . "  `glpi_plugin_activity_holidaytypes`.`is_holiday`, "
                  . "  `glpi_plugin_activity_holidaytypes`.`is_holiday_counter`, "
                  . "  `glpi_plugin_activity_holidaytypes`.`is_sickness`, "
                  . "  `glpi_plugin_activity_holidaytypes`.`is_part_time` "
                  . "FROM `glpi_plugin_activity_holidays` "
                  . "LEFT JOIN `glpi_plugin_activity_holidaytypes`"
                  . "  ON (`glpi_plugin_activity_holidaytypes`.`id` = `glpi_plugin_activity_holidays`.`plugin_activity_holidaytypes_id`)"
                  . "WHERE `glpi_plugin_activity_holidays`.`users_id` = " . $users_id . " "
                  . "AND `glpi_plugin_activity_holidays`.`global_validation` = " . PluginActivityCommonValidation::ACCEPTED . " AND "
                  . "`glpi_plugin_activity_holidays`.`plugin_activity_holidayperiods_id` = '" . $period['id'] . "' "
                  . "AND `is_holiday` = 1 AND `is_holiday_counter` = 1";

         $result = $DB->query($query);

         if ($DB->numrows($result)) {
            while ($data = $DB->fetchArray($result)) {
               $nb_jours['total']                 += $data['actiontime'] / $AllDay;
               $nb_jours['period'][$period['id']] += $data['actiontime'] / $AllDay;
            }
         }
      }
      return $nb_jours;
   }

   /**
    * Counts number of days of holiday between two dates for a user
    *
    * @param type  $users_id
    * @param type  $start
    * @param type  $end
    *
    * @return type
    * @global type $DB
    *
    */

   function countNbHoliday($users_id, $periods, $statut) {
      global $DB;

      $types = [PluginActivityReport::$HOLIDAY, PluginActivityReport::$SICKNESS, PluginActivityReport::$PART_TIME];
      foreach ($types as $type) {
         $nb_jours_i[$type]['total'] = 0;
      }

      foreach ($periods as $period) {

         $AllDay = PluginActivityReport::getAllDay();
         $query  = "SELECT `glpi_plugin_activity_holidays`.`begin` AS begin, 
                     `glpi_plugin_activity_holidays`.`end` AS end, 
                     `glpi_plugin_activity_holidays`.`actiontime` AS actiontime, 
                     `glpi_plugin_activity_holidaytypes`.`id` AS types_id, 
                     `glpi_plugin_activity_holidaytypes`.`name`, 
                     `glpi_plugin_activity_holidaytypes`.`is_holiday`, 
                     `glpi_plugin_activity_holidaytypes`.`is_holiday_counter`, 
                     `glpi_plugin_activity_holidaytypes`.`is_sickness`, 
                     `glpi_plugin_activity_holidaytypes`.`is_part_time` 
                   FROM `glpi_plugin_activity_holidays` 
                   LEFT JOIN `glpi_plugin_activity_holidaytypes`
                     ON (`glpi_plugin_activity_holidaytypes`.`id` = `glpi_plugin_activity_holidays`.`plugin_activity_holidaytypes_id`)
                   LEFT JOIN `glpi_plugin_activity_holidayperiods`
                     ON (`glpi_plugin_activity_holidays`.`plugin_activity_holidayperiods_id` = `glpi_plugin_activity_holidayperiods`.`id`)
                   WHERE `glpi_plugin_activity_holidays`.`global_validation` = $statut  
                   AND `glpi_plugin_activity_holidays`.`users_id` = " . $users_id . " 
                   AND `glpi_plugin_activity_holidayperiods`.`id` = '" . $period['id'] . "'";
         $result = $DB->query($query);

         if ($DB->numrows($result)) {
            while ($data = $DB->fetchArray($result)) {

               if ($data['is_holiday'] && $data['is_holiday_counter']) {
                  $nb_jours_i[PluginActivityReport::$HOLIDAY]['total'] += $data['actiontime'] / $AllDay;
                  if (!isset($nb_jours_i[PluginActivityReport::$HOLIDAY]['sub_types'][$data['types_id']])) {
                     $nb_jours_i[PluginActivityReport::$HOLIDAY]['sub_types'][$data['types_id']] = 0;
                  }
                  $nb_jours_i[PluginActivityReport::$HOLIDAY]['sub_types'][$data['types_id']] += $data['actiontime'] / $AllDay;

               } else if ($data['is_sickness']) {
                  $nb_jours_i[PluginActivityReport::$SICKNESS]['total'] += $data['actiontime'] / $AllDay;

               } else if ($data['is_part_time']) {
                  $nb_jours_i[PluginActivityReport::$PART_TIME]['total'] += $data['actiontime'] / $AllDay;
               }
            }
         }
      }

      // Round total
      foreach ($nb_jours_i as $type => &$holiday) {
         $holiday['total'] = PluginActivityReport::TotalTpsPassesArrondis($holiday['total']);
      }

      return $nb_jours_i;
   }

   function getHolidaysInDays($begin, $end, $AllDay) {
      $holiday = new PluginActivityHoliday();

      $countwe  = $holiday->countWe($begin, $end, PluginActivityHoliday::getCalendarHolidaysArray($_SESSION['glpiactive_entity']));
      $duration = strtotime(date('Y-m-d', strtotime($end)) . '00:00:00') - strtotime(date('Y-m-d', strtotime($begin)) . '00:00:00');

      //real
      $duration = ($duration / 86400) * $AllDay;

      $beginHour = date('H:i:s', strtotime($begin));
      $endHour   = date('H:i:s', strtotime($end));

      // Add hours
      if (strtotime($endHour) - strtotime($beginHour) > $AllDay) {
         $duration += $AllDay;
      } else {
         $duration += strtotime($endHour) - strtotime($beginHour);
         // Case of time between AM_END and PM_BEGIN
         if (strtotime($beginHour) <= strtotime(PluginActivityReport::$AM_END)
             && strtotime($endHour) >= strtotime(PluginActivityReport::$PM_BEGIN)) {
            $duration -= (strtotime(PluginActivityReport::$PM_BEGIN) - strtotime(PluginActivityReport::$AM_END));
         }
      }

      return PluginActivityReport::TotalTpsPassesArrondis($duration / $AllDay) - $countwe;
   }

   /*from lateralmenu*/
   function showHolidayDetailsByType($nbHolidays) {

      //echo "<table style='margin:10px'>";

      foreach ($nbHolidays as $type => $val) {
         if ($val['total'] > 0) {
            echo "<tr><td>" . PluginActivityReport::getHolidayName($type) . "</td><td>" . $val['total'] . "</td></tr>";
            if (isset($val['sub_types'])) {
               foreach ($val['sub_types'] as $subType => $subVal) {
                  echo "<tr>";
                  echo "<td><span class='activity_tree'></span>" . Dropdown::getDropdownName('glpi_plugin_activity_holidaytypes', $subType) . "</td>";
                  echo "<td>" . $subVal . "</td>";
                  //echo "<td>";
                  //$hcount = new PluginActivityHolidaycount();
                  //$count = $hcount->showCountForHolidayType($subType);
                  //echo "(".$count.")";
                  //echo "</td>";
                  echo "</tr>";

               }
            }
         }
      }
      //echo "</table>";
   }

   function getDetails($users_id, $holiday_period_id) {
      $holidaycount = new PluginActivityHolidaycount();
      if ($holidaycount->getFromDBByCrit(['plugin_activity_holidayperiods_id' => $holiday_period_id,
                                          'users_id'                          => $users_id])) {
         $holiday = new PluginActivityHoliday();
         $nbdays  = $holiday->getNbDayByHolidayPeriod($users_id, $holiday_period_id, [PluginActivityCommonValidation::ACCEPTED,
                                                                                      PluginActivityCommonValidation::WAITING]);

         //Total remaining
         $total_remaining_validated = $holidaycount->fields['count'] - $nbdays;

         $holiday         = new PluginActivityHoliday();
         $total_remaining = $holiday->getNbDayByHolidayPeriod($users_id, $holiday_period_id, [PluginActivityCommonValidation::WAITING]);

         echo "<tr id='tr_plugin_activity_details'><td colspan='2'>";
         echo __('Remaining holidays for the period', 'activity') . ": " . $total_remaining_validated . " " . strtolower(_n('Day', 'Days', 2)) . "<br>";
         echo __('Leave awaiting validation', 'activity') . ": " . $total_remaining . " " . strtolower(_n('Day', 'Days', 2));
         echo "</td></tr>";
      }
   }

   function getNbDayByHolidayPeriod($users_id, $holiday_period_id, $states) {
      global $DB;

      $query = "SELECT SUM(`glpi_plugin_activity_holidays`.`actiontime`) AS actiontime
               FROM `glpi_plugin_activity_holidays` 
               WHERE `glpi_plugin_activity_holidays`.`users_id` = $users_id 
               AND `glpi_plugin_activity_holidays`.`global_validation` IN (" . implode(",", $states) . ")
               AND `plugin_activity_holidayperiods_id` = $holiday_period_id";

      $result     = $DB->query($query);
      $actiontime = $DB->result($result, 0, "actiontime");

      $AllDay = PluginActivityReport::getAllDay();
      return $actiontime / $AllDay;

   }
}