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

class PluginActivityHolidaycount extends CommonDBTM {

   var    $dohistory = false;
   var    $holidays  = [];
   static $rightname = "plugin_activity";

   static function getTypeName($nb = 1) {
      return _n('Holiday counter', 'Holiday counters', $nb, 'activity');
   }

   /*
   function cleanDBonPurge() {
      $holidayValidation = new PluginActivityHolidayValidation();
      $holidayValidation->cleanDBonItemDelete($this->getType(),$this->fields['id']);

      parent::cleanDBonPurge();
   }*/


   function defineTabs($options = []) {
      $ong = [];
      $this->addDefaultFormTab($ong);
      return $ong;
   }

   /**
    * @see CommonDBTM::prepareInputForUpdate()
    **/
   function prepareInputForAdd($input) {

      if ($input['plugin_activity_holidayperiods_id'] == 0) {
         Session::addMessageAfterRedirect(__("Holiday period is mandatory field", "activity"), false, ERROR);
         return false;
      }
      if ($input['plugin_activity_holidaytypes_id'] == 0) {
         Session::addMessageAfterRedirect(__('Holiday type is mandatory field', 'activity'), false, ERROR);
         return false;
      }

      $restrict = ["users_id" => $input['users_id'], "plugin_activity_holidayperiods_id" => $input['plugin_activity_holidayperiods_id']];
      $dbu      = new DbUtils();
      $hcounts  = $dbu->getAllDataFromTable($this->getTable(), $restrict);

      if (count($hcounts) > 0) {
         Session::addMessageAfterRedirect(__("Only one counter by period is allowed", "activity"), false, ERROR);
         return false;
      }

      $holidayperiod = new PluginActivityHolidayPeriod();
      $holidayperiod->getFromDB($input['plugin_activity_holidayperiods_id']);
      $input['name'] = $holidayperiod->getName();

      return $input;

   }


   function rawSearchOptions() {

      $holidaytype   = new PluginActivityHolidayType();
      $holidayperiod = new PluginActivityHolidayPeriod();

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
         'table'         => $holidayperiod->getTable(),
         'field'         => 'name',
         'name'          => PluginActivityHolidayPeriod::getTypeName(1),
         'datatype'      => 'dropdown',
         'massiveaction' => false,
      ];

      $tab[] = [
         'id'            => '9',
         'table'         => 'glpi_users',
         'field'         => 'name',
         'name'          => _n('User', 'Users', 1),
         'massiveaction' => false,
         'nosearch'      => true,
         'datatype'      => 'dropdown',
         'right'         => 'interface',
      ];

      $tab[] = [
         'id'       => '10',
         'table'    => $this->getTable(),
         'field'    => 'count',
         'name'     => __('Counter', 'activity'),
         'datatype' => 'decimal'
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
         'id'       => '30',
         'table'    => $this->getTable(),
         'field'    => 'id',
         'name'     => __('ID'),
         'datatype' => 'number'
      ];

      return $tab;
   }


   /**
    * Display the count holiday form
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

      $this->initForm($ID, $options);
      $this->showFormHeader($options);
      $dbu = new DbUtils();

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo _n('User', 'Users', 1);
      echo "</td>";
      echo "<td>";
      echo $dbu->getUserName(Session::getLoginUserID());
      echo "<input type='hidden' name='users_id' value='" . Session::getLoginUserID() . "'>";
      echo "</td>";
      echo "<td>";

      echo PluginActivityHolidayType::getTypeName(1) . "</td><td>";

      $options = [
         'name'     => "plugin_activity_holidaytypes_id",
         'value'    => $this->fields["plugin_activity_holidaytypes_id"],
         'comments' => 1];
      Dropdown::show('PluginActivityHolidayType', $options);

      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>";

      echo PluginActivityHolidayPeriod::getTypeName(1) . "</td><td>";

      $options = [
         'name'     => "plugin_activity_holidayperiods_id",
         'value'    => $this->fields["plugin_activity_holidayperiods_id"],
         'comments' => 1];
      Dropdown::show('PluginActivityHolidayPeriod', $options);

      echo "</td>";

      echo "<td>" . __('Counter', 'activity') . "</td>";
      echo "<td>";
      echo "<input type='text' name='count' value='" . Html::formatNumber($this->fields["count"], true) . "' size='14'>";
      echo "</td>";
      echo "</tr>";

      $this->showFormButtons($options);

      return true;
   }

   function showCountForHolidayType($plugin_activity_holidaytypes_id) {
      global $DB;

      $user_id = Session::getLoginUserID();

      $count = 0;
      // Current year
      $old_annee  = intval(strftime("%Y") - 1);
      $next_annee = date('Y');
      // Next year
      if (time() > strtotime(date('Y') . "-05-31")) {
         $old_annee  = intval(strftime("%Y"));
         $next_annee = intval(strftime("%Y") + 1);
      }

      $query = " SELECT `glpi_plugin_activity_holidaycounts`.*
                              FROM `glpi_plugin_activity_holidaycounts`
                              LEFT JOIN `glpi_plugin_activity_holidayperiods`
                                 ON (`glpi_plugin_activity_holidaycounts`.`plugin_activity_holidayperiods_id` = `glpi_plugin_activity_holidayperiods`.`id`)
                              WHERE `users_id`='" . $user_id . "' 
                                 AND `glpi_plugin_activity_holidaycounts`.`plugin_activity_holidaytypes_id` = '" . $plugin_activity_holidaytypes_id . "'
                                 AND `glpi_plugin_activity_holidayperiods`.`begin` >= '" . $old_annee . "-06-01' 
                                 AND `glpi_plugin_activity_holidayperiods`.`end` <= '" . $next_annee . "-05-31'";

      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if ($number) {
         while ($data = $DB->fetchArray($result)) {

            $count += $data['count'];

         }
      }
      return $count;
   }

   /**
    * Get the current periods with the date
    *
    * @global type $DB
    *
    * @param type  $start
    * @param type  $end
    *
    * @return type
    */
   function getCurrentPeriods() {
      global $DB;

      $hcounts = [];

      $query = " SELECT *
               FROM `glpi_plugin_activity_holidayperiods`
               WHERE NOT `archived`";

      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if ($number) {
         while ($data = $DB->fetchArray($result)) {
            $hcounts[] = $data;
         }
      }
      return $hcounts;
   }

   function showCountForHolidayTypeAndPeriod($period, $periods) {
      global $DB;

      $hcounts = [];
      $user_id = Session::getLoginUserID();

      $period_id = [];
      if (isset($periods['period'])) {
         foreach ($periods['period'] as $key => $data) {
            $period_id[] = $key;
         }
      }

      $query = " SELECT `glpi_plugin_activity_holidaycounts`.*
                              FROM `glpi_plugin_activity_holidaycounts`
                              LEFT JOIN `glpi_plugin_activity_holidayperiods`
                                 ON (`glpi_plugin_activity_holidaycounts`.`plugin_activity_holidayperiods_id` = `glpi_plugin_activity_holidayperiods`.`id`)
                              WHERE `users_id`='" . $user_id . "' 
                                 AND `glpi_plugin_activity_holidayperiods`.`short_name` LIKE '" . $period . "' ";
      if (count($period_id) > 0) {
         $query .= "AND `glpi_plugin_activity_holidaycounts`.`plugin_activity_holidayperiods_id` IN (" . implode(',', $period_id) . ")";
      }

      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if ($number) {
         while ($data = $DB->fetchArray($result)) {
            $hcounts[] = $data;
         }
      }
      return $hcounts;
   }

   /*from lateralmenu*/
   function showHolidayDetailsByPeriod($nbHolidays) {

      if ($nbHolidays['total'] != 0) {
         echo "<tr class='tab_bg_1'><td colspan='2'><b>" . __('Remaining holidays', 'activity') . " : </b></td></tr>";
         $total         = 0;
         $total_holiday = 0;

         $CP       = $this->showCountForHolidayTypeAndPeriod(PluginActivityHolidayType::CP, $nbHolidays);
         $total_CP = 0;
         foreach ($CP as $key => $val) {
            echo "<tr>";
            echo "<td colspan='2'>" . Dropdown::getDropdownName('glpi_plugin_activity_holidayperiods', $val['plugin_activity_holidayperiods_id']) . "</td>";
            echo "</tr>";

            $holidayperiod = new PluginActivityHolidayPeriod();
            $holidayperiod->getFromDB($val['plugin_activity_holidayperiods_id']);

            echo "<tr>";
            echo "<td><span class='activity_tree'></span>";
            echo __('Valid until', 'activity');
            echo "&nbsp;";

            echo Html::convDate($holidayperiod->fields['end']);
            echo "</td>";

            $total_by_period = $val['count'];

            $CP = $total_by_period - $nbHolidays['period'][$val['plugin_activity_holidayperiods_id']];
            echo "<td>";
            echo $CP;
            echo "</td>";
            echo "</tr>";

            $total_CP += $CP;
         }

         $RT        = $this->showCountForHolidayTypeAndPeriod(PluginActivityHolidayType::RTT, $nbHolidays);
         $total_RTT = 0;

         foreach ($RT as $key => $val) {

            $holidayperiod = new PluginActivityHolidayPeriod();
            $holidayperiod->getFromDB($val['plugin_activity_holidayperiods_id']);

            echo "<tr>";
            echo "<td colspan='2'>";
            echo $holidayperiod->getName();
            //echo __('RTT', 'activity');
            echo "</td>";
            echo "</tr>";

            echo "<tr>";
            echo "<td><span class='activity_tree'></span>";
            echo __('Valid until', 'activity');
            echo "&nbsp;";

            echo Html::convDate($holidayperiod->fields['end']);
            echo "</td>";

            $total_by_period = $val['count'];

            $RTT = $total_by_period - $nbHolidays['period'][$val['plugin_activity_holidayperiods_id']];

            echo "<td>";
            echo $RTT;
            echo "</td>";
            echo "</tr>";

            $total_RTT += $RTT;
         }

         echo "<tr>";
         echo "<td>";
         echo __('Cumul', 'activity');
         echo "</td>";

         echo "<td>";
         echo $total_CP + $total_RTT;
         echo "</td>";
         echo "</tr>";
      }
   }

}