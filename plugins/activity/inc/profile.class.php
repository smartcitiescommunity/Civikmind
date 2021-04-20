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

class PluginActivityProfile extends Profile {

   static $rightname = "profile";

   static function getTypeName($nb = 0) {
      return _n('Right management', 'Rights management', $nb, 'activity');
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if ($item->getType() == 'Profile') {
         return PluginActivityPlanningExternalEvent::getTypeName(2);
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      global $CFG_GLPI;

      if ($item->getType() == 'Profile') {
         $ID   = $item->getID();
         $prof = new self();

         self::addDefaultProfileInfos($ID, ['plugin_activity'                    => 0,
                                                 'plugin_activity_statistics'         => 0,
                                                 'plugin_activity_can_requestholiday' => 0,
                                                 'plugin_activity_can_validate'       => 0,
                                                 'plugin_activity_all_users'          => 0]);
         $prof->showForm($ID);
      }
      return true;
   }

   /**
    * Show profile form
    *
    * @param $items_id integer id of the profile
    * @param $target value url of target
    *
    * @return nothing
    * */
   function showForm($profiles_id = 0, $openform = true, $closeform = true) {

      echo "<div class='firstbloc'>";
      if (($canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE])) && $openform) {
         $profile = new Profile();
         echo "<form method='post' action='".$profile->getFormURL()."'>";
      }

      $profile = new Profile();
      $profile->getFromDB($profiles_id);

      $rights = $this->getAllRights();
      $profile->displayRightsChoiceMatrix($rights, ['canedit'       => $canedit,
                                                    'default_class' => 'tab_bg_2',
                                                    'title'         => __('General')]);

      echo "<table class='tab_cadre_fixehov'>";
      $effective_rights = ProfileRight::getProfileRights($profiles_id, ['plugin_activity_can_requestholiday',
                                                                             'plugin_activity_can_validate',
                                                                             'plugin_activity_statistics',
                                                                             'plugin_activity_all_users']);
      echo "<tr class='tab_bg_2'>";
      echo "<td width='20%'>".__('Create a holiday request', 'activity')."</td>";
      echo "<td colspan='5'>";
      Html::showCheckbox(['name'    => '_plugin_activity_can_requestholiday[1_0]',
                               'checked' => $effective_rights['plugin_activity_can_requestholiday']]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'>";
      echo "<td width='20%'>"._n('Validate holiday', 'Validate holidays', 2, 'activity')."</td>";
      echo "<td colspan='5'>";
      Html::showCheckbox(['name'    => '_plugin_activity_can_validate[1_0]',
                               'checked' => $effective_rights['plugin_activity_can_validate']]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'>";
      echo "<td width='20%'>".__('Statistics and reports', 'activity')."</td>";
      echo "<td colspan='5'>";
      Html::showCheckbox(['name'    => '_plugin_activity_statistics[1_0]',
                               'checked' => $effective_rights['plugin_activity_statistics']]);
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_2'>";
      echo "<td width='20%'>".__('Display activities of all', 'activity')."</td>";
      echo "<td colspan='5'>";
      Html::showCheckbox(['name'    => '_plugin_activity_all_users[1_0]',
                               'checked' => $effective_rights['plugin_activity_all_users']]);
      echo "</td></tr>\n";
      echo "</table>";

      if ($canedit && $closeform) {
         echo "<div class='center'>";
         echo Html::hidden('id', ['value' => $profiles_id]);
         echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
         echo "</div>\n";
         Html::closeForm();
      }
      echo "</div>";

      $this->showLegend();
   }

   static function getAllRights($all = false) {
      $rights = [
         ['itemtype' => 'PluginActivityPlanningExternalEvent',
               'label'    => _n('Activity', 'Activities', 1, 'activity'),
               'field'    => 'plugin_activity'
         ]
      ];

      if ($all) {
         $rights[] = ['itemtype' => 'PluginActivityHoliday',
                           'label'    => __('Create a holiday request', 'activity'),
                           'field'    => 'plugin_activity_can_requestholiday'];

         $rights[] = ['itemtype' => 'PluginActivityHoliday',
                           'label'    => _n('Validate holiday', 'Validate holidays', 2, 'activity'),
                           'field'    => 'plugin_activity_can_validate'];

         $rights[] = ['itemtype' => 'PluginActivityPlanningExternalEvent',
                           'label'    => __('Statistics and reports', 'activity'),
                           'field'    => 'plugin_activity_statistics'];

         $rights[] = ['itemtype' => 'PluginActivityPlanningExternalEvent',
                           'label'    => __('Display activities of all', 'activity'),
                           'field'    => 'plugin_activity_all_users'];
      }

      return $rights;
   }

   /**
    * Init profiles
    *
    * */
   static function translateARight($old_right) {
      switch ($old_right) {
         case '':
            return 0;
         case 'r' :
            return READ;
         case 'w':
            return ALLSTANDARDRIGHT;
         case '0':
         case '1':
            return $old_right;

         default :
            return 0;
      }
   }

   /**
    * @since 0.85
    * Migration rights from old system to the new one for one profile
    * @param $profiles_id the profile ID
    * @return bool
    */
   static function migrateOneProfile() {
      global $DB;
      //Cannot launch migration if there's nothing to migrate...
      if (!$DB->tableExists('glpi_plugin_activity_profiles')) {
         return true;
      }
      $dbu = new DbUtils();
      $datas = $dbu->getAllDataFromTable('glpi_plugin_activity_profiles');

      foreach ($datas as $profile_data) {
         $matching       = ['activity'           => 'plugin_activity',
                                 'all_users'          => 'plugin_activity_all_users',
                                 'statistics'         => 'plugin_activity_statistics',
                                 'can_requestholiday' => 'plugin_activity_can_requestholiday',
                                 'can_validate'       => 'plugin_activity_can_validate'];
         // Search existing rights
         $used = [];
         $existingRights = $dbu->getAllDataFromTable('glpi_profilerights', ["profiles_id" => $profile_data['profiles_id']]);
         foreach ($existingRights as $right) {
            $used[$right['profiles_id']][$right['name']] = $right['rights'];
         }

         // Add or update rights
         foreach ($matching as $old => $new) {
            if (isset($used[$profile_data['profiles_id']][$new])) {
               $query = "UPDATE `glpi_profilerights` 
                         SET `rights`='".self::translateARight($profile_data[$old])."' 
                         WHERE `name`='$new' AND `profiles_id`='".$profile_data['profiles_id']."'";
               $DB->query($query);
            } else {
               $query = "INSERT INTO `glpi_profilerights` (`profiles_id`, `name`, `rights`) VALUES ('".$profile_data['profiles_id']."', '$new', '".self::translateARight($profile_data[$old])."');";
               $DB->query($query);
            }
         }
      }
   }

   /**
    * Initialize profiles, and migrate it necessary
    */
   static function initProfile() {
      global $DB;
      $profile = new self();
      $dbu     = new DbUtils();

      //Add new rights in glpi_profilerights table
      foreach ($profile->getAllRights(true) as $data) {
         if ($dbu->countElementsInTable("glpi_profilerights", ["name" => $data['field']]) == 0) {
            ProfileRight::addProfileRights([$data['field']]);
         }
      }

      // Migration old rights in new ones
      self::migrateOneProfile();

      foreach ($DB->request("SELECT *
                             FROM `glpi_profilerights` 
                             WHERE `profiles_id`='".$_SESSION['glpiactiveprofile']['id']."' 
                             AND `name` LIKE '%plugin_activity%'") as $prof) {
         $_SESSION['glpiactiveprofile'][$prof['name']] = $prof['rights'];
      }
   }

   static function createFirstAccess($profiles_id) {
      self::addDefaultProfileInfos($profiles_id, ['plugin_activity'                    => ALLSTANDARDRIGHT,
                                                       'plugin_activity_statistics'         => 1,
                                                       'plugin_activity_all_users'          => 1,
                                                       'plugin_activity_can_requestholiday' => 1,
                                                       'plugin_activity_can_validate'       => 1], true);
   }

   static function removeRightsFromSession() {
      foreach (self::getAllRights(true) as $right) {
         if (isset($_SESSION['glpiactiveprofile'][$right['field']])) {
            unset($_SESSION['glpiactiveprofile'][$right['field']]);
         }
      }
   }

   static function removeRightsFromDB() {
      $plugprof = new ProfileRight();
      foreach (self::getAllRights(true) as $right) {
         $plugprof->deleteByCriteria(['name' => $right['field']]);
      }
   }

   /**
    * @param $profile
    * */
   static function addDefaultProfileInfos($profiles_id, $rights, $drop_existing = false) {
      global $DB;

      $dbu          = new DbUtils();
      $profileRight = new ProfileRight();
      foreach ($rights as $right => $value) {
         if ($dbu->countElementsInTable('glpi_profilerights', ["profiles_id" => $profiles_id, "name" => $right]) && $drop_existing) {
            $profileRight->deleteByCriteria(['profiles_id' => $profiles_id, 'name' => $right]);
         }
         if (!$dbu->countElementsInTable('glpi_profilerights', ["profiles_id" => $profiles_id, "name" => $right])) {
            $myright['profiles_id'] = $profiles_id;
            $myright['name']        = $right;
            $myright['rights']      = $value;
            $profileRight->add($myright);

            //Add right to the current session
            $_SESSION['glpiactiveprofile'][$right] = $value;
         }
      }
   }

}