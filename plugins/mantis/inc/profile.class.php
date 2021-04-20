<?php
/**
 * --------------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of mantis.
 *
 * mantis is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * mantis is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * --------------------------------------------------------------------------
 * @author    François Legastelois
 * @copyright Copyright (C) 2018 Teclib
 * @license   AGPLv3+ http://www.gnu.org/licenses/agpl.txt
 * @link      https://github.com/pluginsGLPI/mantis
 * @link      https://pluginsglpi.github.io/mantis/
 * -------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginMantisProfile to manage the profiles
 */
class PluginMantisProfile extends CommonDBTM {

   // Necessary rights to edit the rights of this plugin
   static $rightname = "profile";

   /**
    * @see CommonGLPI::getTabNameForItem()
   **/
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if ($item->getType()=='Profile') {
            return PluginMantisMantis::getTypeName();
      }
      return '';
   }

   /**
    * Describe all possible rights for the plugin
    * @return array
   **/
   static function getAllRights() {

      $rights = [
          ['itemtype'  => 'PluginMantisProfile',
           'label'     => __('Use the plugin MantisBT', 'mantis'),
           'field'     => 'plugin_mantis_use',
           'rights'    =>  [READ   => __('Read'),
                            UPDATE => __('Update')],
           'default'   => 3]];
      return $rights;
   }

   /**
    * addDefaultProfileInfos
    * @param $profiles_id
    * @param $rights
   **/
   static function addDefaultProfileInfos($profiles_id, $rights) {
      $profileRight = new ProfileRight();
      foreach ($rights as $right => $value) {
         if (!countElementsInTable('glpi_profilerights',
                                   ['profiles_id' => $profiles_id, 'name' => $right])) {
            $myright['profiles_id'] = $profiles_id;
            $myright['name']        = $right;
            $myright['rights']      = $value;
            $profileRight->add($myright);
            //Add right to the current session
            $_SESSION['glpiactiveprofile'][$right] = $value;
         }
      }
   }

   /**
    * @see CommonGLPI::displayTabContentForItem()
   **/
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      global $CFG_GLPI;

      if ($item->getType()=='Profile') {
         $ID = $item->getID();
         $prof = new self();
         //In case there's no right for this profile, create it
         foreach (self::getAllRights() as $right) {
            self::addDefaultProfileInfos($ID, [$right['field'] => 0]);
         }
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
   **/
   function showForm($profiles_id = 0, $openform = true, $closeform = true) {

      echo "<div class='firstbloc'>";
      if (($canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE]))
          && $openform) {
         $profile = new Profile();
         echo "<form method='post' action='".$profile->getFormURL()."'>";
      }

      $profile = new Profile();
      $profile->getFromDB($profiles_id);

      $profile->displayRightsChoiceMatrix($this->getAllRights(),
                                                ['canedit'       => $canedit,
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
            return UPDATE + READ;
         case '0':
         case '1':
            return $old_right;

         default :
            return 0;
      }
   }

   /**
    * Initialize profiles, and migrate it necessary
    */
   static function migrateAllProfiles() {
      global $DB;

      foreach ($DB->request("SELECT `id` FROM `glpi_profiles`") as $prof) {
         self::migrateOneProfile($prof['id']);
      }
   }

   /**
    * @since 0.85
    * Migration rights from old system to the new one for one profile
    * @param $profiles_id the profile ID
    */
   static function migrateOneProfile($profiles_id) {
      global $DB;

      $table = "glpi_plugin_mantis_profiles";

      if (!$DB->tableExists($table)) {
         return true;
      }

      foreach ($DB->request($table, "`id`='$profiles_id'") as $profile_data) {
         $translatedRight = self::translateARight($profile_data["right"]);
         ProfileRight::updateProfileRights($profiles_id, ['plugin_mantis_use' => $translatedRight]);
      }
   }

   /**
    * Change active profile to the $ID one. Update glpiactiveprofile session variable.
    *
    * @param $ID : ID of the new profile
    *
    * @return Nothing
   **/
   static function changeProfile() {
      global $DB;

      foreach ($DB->request("SELECT *
                           FROM `glpi_profilerights`
                           WHERE `profiles_id`='".$_SESSION['glpiactiveprofile']['id']."'
                              AND `name` = 'plugin_mantis_use'") as $prof) {
         $_SESSION['glpiactiveprofile'][$prof['name']] = $prof['rights'];
      }
   }

   /**
   * Test if old right name exists (mantis:mantis)
   * @return bool
   */
   static function oldRightNameExists() {
      if (countElementsInTable(ProfileRight::getTable(), ['name' => 'mantis:mantis']) > 0) {
         return true;
      }
      return false;
   }

   /**
   * Update old right name (mantis:mantis to plugin_mantis_use)
   * @return nothing
   */
   static function updateOldRightName() {
      global $DB;

      $query = "UPDATE ".ProfileRight::getTable()."
                  SET name = 'plugin_mantis_use
                  WHERE name = 'mantis:mantis'";
      $DB->query($query);
   }

   /**
    * Install all necessary profiles for the plugin
    *
    * @return boolean True if success
    */
   static function install(Migration $migration) {
      global $DB;

      if (self::oldRightNameExists()) {
         self::updateOldRightName();
      }

      if ($DB->tableExists("glpi_plugin_mantis_profiles")) {
         self::migrateAllProfiles();
         $migration->dropTable("glpi_plugin_mantis_profiles");
         return true;
      }

      // Set default rights
      foreach (self::getAllRights() as $right) {
         self::addDefaultProfileInfos($_SESSION['glpiactiveprofile']['id'],
                                       [$right['field'] => $right['default']]);
      }

   }

   /**
    * Uninstall previously installed profiles of the plugin
    *
    * @return boolean True if success
    */
   static function uninstall() {
      global $DB;

      foreach (self::getAllRights() as $right) {
         $query = "DELETE FROM `glpi_profilerights`
                   WHERE `name` = '".$right['field']."'";
         $DB->query($query);

         if (isset($_SESSION['glpiactiveprofile'][$right['field']])) {
            unset($_SESSION['glpiactiveprofile'][$right['field']]);
         }
      }
   }
}
