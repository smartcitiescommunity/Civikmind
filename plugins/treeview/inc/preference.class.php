<?php
/*
 * @version $Id: setup.php 313 2011-12-19 09:39:58Z remi $
 -------------------------------------------------------------------------
 treeview - TreeView browser plugin for GLPI
 Copyright (C) 2003-2012 by the treeview Development Team.

 https://forge.indepnet.net/projects/treeview
 -------------------------------------------------------------------------

 LICENSE

 This file is part of treeview.

 treeview is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 treeview is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with treeview. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * class plugin_treeview_preference
 * Load and store the preference configuration from the database
**/
class PluginTreeviewPreference extends CommonDBTM {

   static function getMenuContent() {
      $menu          = [];
      $menu['title'] = __('Tree view', 'treeview');
      $menu['page']  = '/' . Plugin::getWebDir('treeview', false) . '/index.php';
      $menu['icon']  = 'fas fa-sitemap';
      return $menu;
   }

   function showFormUserPreference($target, $id) {

      $data = plugin_version_treeview();
      $this->getFromDB($id);
      echo "<form action='".$target."' method='post'>";
      echo "<table class='tab_cadre_fixe' cellpadding='5'>";
      echo "<tr><th colspan='2'>" .sprintf(__('%1$s - %2$s'), $data['name'], $data['version']);
      echo "</th></tr>";

      echo "<tr class='tab_bg_1 center'>";
      echo "<td>".__('Launch the plugin Treeview with GLPI launching', 'treeview')."</td>";
      echo "<td>";
      Dropdown::showYesNo("show_on_load", $this->fields["show_on_load"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1 center'><td colspan='2'>";
      echo "<input type='submit' name='plugin_treeview_user_preferences_save' value='".
             _sx('button', 'Post')."' class='submit'>";
      echo "<input type='hidden' name='id' value='$id'></td></tr>";

      echo "<tr class='tab_bg_1 center'>";
      echo "<td colspan='2'>".__('Warning: If there are more than one plugin which be loaded at startup, then only the first will be used', 'treeview');
      echo "</td></tr>";

      echo "</table>";
      Html::closeForm();
   }


   function checkIfPreferenceExists($users_id) {
      global $DB;

      $result = $DB->query("SELECT `id`
                            FROM `glpi_plugin_treeview_preferences`
                            WHERE `users_id` = '$users_id'");
      if ($DB->numrows($result) > 0) {
         return $DB->result($result, 0, "id");
      }
      return 0;
   }


   function addDefaultPreference($users_id) {

      $input["users_id"]     = $users_id;
      $input["show_on_load"] = 0;

      return $this->add($input);
   }


   function checkPreferenceValue($users_id) {
      global $DB;

      $result = $DB->query("SELECT *
                            FROM `glpi_plugin_treeview_preferences`
                            WHERE `users_id` = '$users_id' ");
      if ($DB->numrows($result) > 0) {
         return $DB->result($result, 0, "show_on_load");
      }
      return 0;
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if ($item->getType() == 'Preference') {
         return __('Tree view', 'treeview');
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if ($item->getType() == 'Preference') {
         $pref = new self();
         $pref_ID = $pref->checkIfPreferenceExists(Session::getLoginUserID());
         if (!$pref_ID) {
             $pref_ID = $pref->addDefaultPreference(Session::getLoginUserID());
         }
         $pref->showFormUserPreference($pref->getFormURL(), $pref_ID);
      }
      return true;
   }
}