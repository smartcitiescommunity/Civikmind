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

/**
 * class plugin_activity_preference
 * Load and store the preference configuration from the database
 */
class PluginActivityPreference extends CommonDBTM {

   static $rightname = "plugin_activity";

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if ($item->getType()=='Preference') {
            return _n('Activity', 'Activities', 1, 'activity');
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      global $CFG_GLPI;
      $pref = new PluginActivityPreference();
      $pref->showForm(Session::getLoginUserID());
      return true;
   }

   function showForm($user_id) {
      global $CFG_GLPI;

      $dbu  = new DbUtils();
      $rand = mt_rand();

      $use_groupmanager = 0;
      $opt = new PluginActivityOption();
      $opt->getFromDB(1);
      if ($opt) {
         $use_groupmanager = $opt->fields['use_groupmanager'];
      }

      if ($use_groupmanager == 0) {
         // Liste des managers déclarés
         $restrict = ["users_id" => $user_id];
         $dbu = new DbUtils();
         $managers = $dbu->getAllDataFromTable('glpi_plugin_activity_preferences', $restrict);

         echo "<form method='post' action='".Toolbox::getItemTypeFormURL("PluginActivityPreference")."'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1' >";
         echo "<th colspan='2'>".__('List of my managers', 'activity')."</th>";
         echo "</tr>";
         if (sizeof($managers) == 0) {
            echo "<tr class='tab_bg_1' id='no_manager_left'>";
            echo "<td colspan='2'>".__('You have not declared any manager yet.', 'activity')."</td>";
            echo "</tr>";
         } else {
            echo "<tr class='tab_bg_1'>";
            echo "<th>"._n('User', 'Users', 1)."</th>";
            echo "<th>"._n('Action', 'Actions', 1)."</th>";
            echo "</tr>";
            foreach ($managers as $manager) {
               echo "<tr class='tab_bg_1'>";
               echo "<td>".$dbu->getUserName($manager['users_id_validate'])."</td>";
               echo "<td>";
               echo "<input type='hidden' name='id' value='".$manager['id']."'>";
               echo "<input type='submit' name='delete' value=\""._sx('button',
                     'Delete permanently')."\"
                            class='submit'></td>";
               echo "</tr>";
            }
         }
         echo "</table>";
         Html::closeForm();

      } else {
         $groupusers = Group_User::getUserGroups($user_id);
         $groups = [];
         foreach ($groupusers as $groupuser) {
            $groups[] = $groupuser["id"];
         }
         $dbu = new DbUtils();
         $restrict = ["groups_id" => [implode(',', $groups)],
                     "is_manager" => 1,
                      "NOT" => ["users_id"  => $user_id]];
         $managers = $dbu->getAllDataFromTable('glpi_groups_users', $restrict);

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1' >";
         echo "<th colspan='2'>".__('List of my managers', 'activity')."</th>";
         echo "</tr>";

         if (sizeof($managers) <= 0) {
            echo "<tr class='tab_bg_1' id='no_manager_left'>";
            echo "<td colspan='2'>".__('You have not declared any manager yet.', 'activity')."</td>";
            echo "</tr>";
         } else {
            echo "<tr class='tab_bg_1'>";
            echo "<th>"._n('User', 'Users', count($managers))."</th>";
            echo "</tr>";
            foreach ($managers as $manager) {
               echo "<tr class='tab_bg_1'>";
               echo "<td>".$dbu->getUserName($manager['users_id'])."</td>";
               echo "</tr>";
            }
         }

         echo "</table>";
      }

         $this->showAddManagerView($managers);
   }

   public function showAddManagerView($managers) {
      global $CFG_GLPI;

      $use_groupmanager = 0;
      $opt = new PluginActivityOption();
      $opt->getFromDB(1);
      if ($opt) {
         $use_groupmanager = $opt->fields['use_groupmanager'];
      }

      if ($use_groupmanager == 0) {

         echo "<br/>";
         echo "<form method='post' action='".Toolbox::getItemTypeFormURL("PluginActivityPreference")."'>";
         echo "<table class='tab_cadre_fixe'> ";
         echo "<tr class='tab_bg_1' >";
         echo "<th colspan='2'>".__('Add a manager', 'activity')."</th>";
         echo "</tr>";

         echo "<tr class='tab_bg_1' >";
         echo "<td>";
         $used = [Session::getLoginUserID()];

         foreach ($managers as $manager) {
            $used[] = $manager['users_id_validate'];
         }

         $rand = User::dropdown([
               'name' => 'users_id_validate',
               'entity' => $_SESSION['glpiactiveentities'],
               'right' => 'all',
               'used' => $used]);

         echo "</td>";
         echo "<td>";
         echo "<input type='hidden' name='users_id' value='".Session::getLoginUserID()."'>";
         echo "<input type='submit' name='add' value=\""._sx('button',
                     'Add')."\"
                            class='submit'></td>";
         echo "</tr>";

         echo "</table>";
         Html::closeForm();
      }
   }
}