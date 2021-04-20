<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Manageentities plugin for GLPI
 Copyright (C) 2014-2017 by the Manageentities Development Team.

 https://github.com/InfotelGLPI/manageentities
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Manageentities.

 Manageentities is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Manageentities is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Manageentities. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginManageentitiesProfile extends Profile {

   static function getTypeName($nb = 0) {
      return _n('Right management', 'Rights management', $nb, 'manageentities');
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if ($item->getType() == 'Profile') {
         return __('Entities portal', 'manageentities');
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if ($item->getType() == 'Profile') {
         $ID   = $item->getID();
         $prof = new self();

         self::addDefaultProfileInfos($ID,
                                      ['plugin_manageentities'            => ALLSTANDARDRIGHT,
                                       'plugin_manageentities_cri_create' => ALLSTANDARDRIGHT]);
         $prof->showForm($ID);
      }

      return true;
   }

   function showForm($profiles_id = 0, $openform = TRUE, $closeform = TRUE) {

      echo "<div class='firstbloc'>";
      if (($canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE]))
          && $openform) {
         $profile = new Profile();
         echo "<form method='post' action='" . $profile->getFormURL() . "'>";
      }

      $profile = new Profile();
      $profile->getFromDB($profiles_id);

      $rights = $this->getAllRights();
      $profile->displayRightsChoiceMatrix($rights, ['canedit'       => $canedit,
                                                    'default_class' => 'tab_bg_2',
                                                    'title'         => __('General')]);
      if ($canedit
          && $closeform) {
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
         ['itemtype' => 'PluginManageentitiesEntity',
          'label'    => __('Entities portal', 'manageentities'),
          'field'    => 'plugin_manageentities'
         ],
         ['itemtype' => 'PluginManageentitiesCriDetail',
          'label'    => _n('Intervention report', 'Intervention reports', 1, 'manageentities'),
          'field'    => 'plugin_manageentities_cri_create'
         ]
      ];

      return $rights;
   }

   /**
    * Init profiles
    *
    **/

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
    * @param $profiles_id the profile ID
    *
    * @since 0.85
    * Migration rights from old system to the new one for one profile
    *
    */
   static function migrateOneProfile() {
      global $DB;
      //Cannot launch migration if there's nothing to migrate...
      if (!$DB->tableExists('glpi_plugin_manageentities_profiles')) {
         return true;
      }
      $dbu   = new DbUtils();
      $datas = $dbu->getAllDataFromTable('glpi_plugin_manageentities_profiles');

      foreach ($datas as $profile_data) {
         $matching = ['manageentities' => 'plugin_manageentities',
                      'cri_create'     => 'plugin_manageentities_cri_create'];
         // Search existing rights
         $used           = [];
         $existingRights = $dbu->getAllDataFromTable('glpi_profilerights', ["`profiles_id`" => $profile_data['profiles_id']]);
         foreach ($existingRights as $right) {
            $used[$right['profiles_id']][$right['name']] = $right['rights'];
         }

         // Add or update rights
         foreach ($matching as $old => $new) {
            if (isset($used[$profile_data['profiles_id']][$new])) {
               $query = "UPDATE `glpi_profilerights` 
                         SET `rights`='" . self::translateARight($profile_data[$old]) . "' 
                         WHERE `name`='$new' AND `profiles_id`='" . $profile_data['profiles_id'] . "'";
               $DB->query($query);
            } else {
               $query = "INSERT INTO `glpi_profilerights` (`profiles_id`, `name`, `rights`) VALUES ('" . $profile_data['profiles_id'] . "', '$new', '" . self::translateARight($profile_data[$old]) . "');";
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
         if ($dbu->countElementsInTable("glpi_profilerights",
                                        ["name" => $data['field']]) == 0) {
            ProfileRight::addProfileRights([$data['field']]);
         }
      }

      // Migration old rights in new ones
      self::migrateOneProfile();

      foreach ($DB->request("SELECT *
                           FROM `glpi_profilerights` 
                           WHERE `profiles_id`='" . $_SESSION['glpiactiveprofile']['id'] . "' 
                              AND `name` LIKE '%plugin_manageentities%'") as $prof) {
         $_SESSION['glpiactiveprofile'][$prof['name']] = $prof['rights'];
      }
   }

   /**
    * Initialize profiles, and migrate it necessary
    */
   static function changeProfile() {
      global $DB;

      foreach ($DB->request("SELECT *
                           FROM `glpi_profilerights` 
                           WHERE `profiles_id`='" . $_SESSION['glpiactiveprofile']['id'] . "' 
                              AND `name` LIKE '%plugin_manageentities%'") as $prof) {
         $_SESSION['glpiactiveprofile'][$prof['name']] = $prof['rights'];
      }

   }

   static function createFirstAccess($profiles_id) {
      self::addDefaultProfileInfos($profiles_id,
                                   ['plugin_manageentities'            => ALLSTANDARDRIGHT,
                                    'plugin_manageentities_cri_create' => ALLSTANDARDRIGHT], true);

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
    **/
   static function addDefaultProfileInfos($profiles_id, $rights, $drop_existing = false) {

      $profileRight = new ProfileRight();
      $dbu          = new DbUtils();

      foreach ($rights as $right => $value) {
         if ($dbu->countElementsInTable('glpi_profilerights',
                                        ["profiles_id" => $profiles_id,
                                         "name"        => $right]) && $drop_existing) {
            $profileRight->deleteByCriteria(['profiles_id' => $profiles_id, 'name' => $right]);
         }
         if (!$dbu->countElementsInTable('glpi_profilerights',
                                         ["profiles_id" => $profiles_id,
                                          "name"        => $right])) {
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