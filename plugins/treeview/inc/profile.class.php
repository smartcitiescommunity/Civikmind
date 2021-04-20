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

class PluginTreeviewProfile extends CommonDBTM {


   static function createFirstAccess($ID) {

      $firstProf = new self();
      if (!$firstProf->GetfromDB($ID)) {
         $profile = new Profile();
         $profile->getFromDB($ID);
         $name = addslashes($profile->fields["name"]);

         $firstProf->add(['id'        => $ID,
                          'name'      => $name,
                          'treeview'  => 'r']);
      }
   }


   function createAccess($profile) {

      return $this->add(['id'   => $profile->getField('id'),
                         'name' => addslashes($profile->getField('name'))]);
   }


   static function changeProfile() {

      $prof = new self();
      if ($prof->getFromDB($_SESSION['glpiactiveprofile']['id'])) {
         $_SESSION["glpi_plugin_treeview_profile"] = $prof->fields;
      } else {
         unset($_SESSION["glpi_plugin_treeview_profile"]);
      }

      //require 'preference.class.php';
      $Pref = new PluginTreeviewPreference();
      $pref_value = $Pref->checkPreferenceValue(Session::getLoginUserID());
      if ($pref_value==1) {
         $_SESSION["glpi_plugin_treeview_preference"] = 1;
      } else {
         unset($_SESSION["glpi_plugin_treeview_preference"]);
      }
   }


   /**
    * profiles modification
   **/
   function showForm($id, $options = []) {

      $target = $this->getFormURL();
      if (isset($options['target'])) {
         $target = $options['target'];
      }

      if (!Session::haveRight("profile", READ)) {
         return false;
      }
      $canedit = Session::haveRight("profile", UPDATE);
      $prof = new Profile();
      if ($id) {
         $this->getFromDB($id);
         $prof->getFromDB($id);
      }

      echo "<form action='".$target."' method='post'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='2' class='center b'>".sprintf(__('%1$s %2$s'), __('Rights management'),
                                                           $this->fields["name"]);
      echo "</th></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>".__('Use the tree', 'treeview')."</td><td>";
      Profile::dropdownRight("treeview",
                             ['value'   => $this->fields["treeview"],
                              'nonone'  => 0,
                              'noread'  => 0,
                              'nowrite' => 1]);
      echo "</td></tr>";

      if ($canedit) {
         echo "<tr class='tab_bg_1'>";
         echo "<td class='center' colspan='2'>";
         echo "<input type='hidden' name='id' value=$id>";
         echo "<input type='submit' name='update_user_profile' value='"._sx('button', 'Update')."'
                class='submit'>";
         echo "</td></tr>";
      }
      echo "</table>";
      Html::closeForm();
   }


   static function cleanProfiles(Profile $prof) {

      $plugprof = new self();
      $plugprof->delete(['id' => $prof->getField("id")]);
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if ($item->getType() == 'Profile') {
         return __('Tree view', 'treeview');
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if ($item->getType() == 'Profile') {
         $prof = new self();
         $ID = $item->getField('id');
         if (!$prof->GetfromDB($ID)) {
            $prof->createAccess($item);
         }
         $prof->showForm($ID);
      }
      return true;
   }
}