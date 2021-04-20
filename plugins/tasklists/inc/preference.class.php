<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Tasklists plugin for GLPI
 Copyright (C) 2003-2016 by the Tasklists Development Team.

 https://github.com/InfotelGLPI/tasklists
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Tasklists.

 Tasklists is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Tasklists is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Tasklists. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/**
 * Class PluginTasklistsPreference
 */
class PluginTasklistsPreference extends CommonDBTM {

   static $rightname = 'plugin_tasklists';

   /**
    * @param CommonGLPI $item
    * @param int        $withtemplate
    *
    * @return string|translated
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if (Session::haveRight('plugin_tasklists', READ)
          && $item->getType() == 'Preference') {
         return __('Tasks list', 'tasklists');
      }
      return '';
   }

   /**
    * @param CommonGLPI $item
    * @param int        $tabnum
    * @param int        $withtemplate
    *
    * @return bool
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      $pref = new self();
      $pref->showForm(Session::getLoginUserID());
      return true;
   }

   /**
    * @param $user_id
    */
   function showForm($user_id) {
      //If user has no preferences yet, we set default values
      if (!$this->getFromDB($user_id)) {
         $this->initPreferences($user_id);
         $this->getFromDB($user_id);
      }

      //Preferences are not deletable
      $options['candel']  = false;
      $options['colspan'] = 1;

      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'><td>" . __("Context by default", "tasklists") . "</td>";
      echo "<td>";
      $types = PluginTasklistsTypeVisibility::seeAllowedTypes();
      Dropdown::show('PluginTasklistsTaskType', ['name'      => "default_type",
                                                 'value'     => $this->fields['default_type'],
                                                 'condition' => ["id" => $types]]);
      echo "</td>";
      echo "</tr>";
      echo "<tr class='tab_bg_1'><td>" . __("Automatic refreshing of tasklist", "tasklists") . "</td>";
      echo "<td>";
      Dropdown::showYesNo("automatic_refresh", $this->fields['automatic_refresh']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'><td>" . __("Refresh every ", "tasklists") . "</td>";
      echo "<td>";
      Dropdown::showFromArray("automatic_refresh_delay", [1 => 1, 2 => 2, 5 => 5, 10 => 10, 30 => 30, 60 => 60],
                              ["value" => $this->fields['automatic_refresh_delay']]);
      echo " " . __('minute(s)', "mydashboard");
      echo "</td>";
      echo "</tr>";

      $this->showFormButtons($options);
   }

   /**
    * @param $users_id
    */
   public function initPreferences($users_id) {

      $input                 = [];
      $input['id']           = $users_id;
      $input['default_type'] = "0";
      $this->add($input);

   }

   /**
    * @param $users_id
    *
    * @return int
    */
   public static function checkDefaultType($users_id) {
      return self::checkPreferenceValue('default_type', $users_id);
   }

   /**
    * @param     $field
    * @param int $users_id
    *
    * @return int
    */
   public static function checkPreferenceValue($field, $users_id = 0) {
      $dbu  = new DbUtils();
      $data = $dbu->getAllDataFromTable($dbu->getTableForItemType(__CLASS__), ["id" => $users_id]);
      if (!empty($data)) {
         $first = array_pop($data);
         if ($field != "default_type") {
            return $first[$field];
         }
         if ($first[$field] > 0) {
            return $first[$field];
         } else {
            $values = PluginTasklistsTaskType::getAllForKanban();
            $data   = [];
            foreach ($values as $key => $value) {
               if (PluginTasklistsTypeVisibility::isUserHaveRight($key)) {
                  $data[] = $key;
               }
            }
            if (!empty($data)) {
               $first = reset($data);
               return $first;
            } else {
               return 0;
            }
         }
      } else {
         $values = PluginTasklistsTaskType::getAllForKanban();
         $data   = [];
         foreach ($values as $key => $value) {
            if (PluginTasklistsTypeVisibility::isUserHaveRight($key)) {
               $data[] = $key;
            }
         }
         if (!empty($data)) {
            $first = reset($data);
            return $first;
         } else {
            return 0;
         }
      }
   }
}
