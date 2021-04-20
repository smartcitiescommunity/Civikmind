<?php
/*
 * @version $Id: preference.class.php 168 2014-08-28 09:04:20Z mtpl $
 LICENSE

 This file is part of the uninstall plugin.

 Uninstall plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Uninstall plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with uninstall. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   uninstall
 @author    the uninstall plugin team
 @copyright Copyright (c) 2010-2013 Uninstall plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/uninstall
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */


class PluginUninstallPreference extends CommonDBTM {

   static $rightname = "uninstall:replace";

   function showFormUserPreferences() {
      global $DB;

      $entity     = $_SESSION['glpiactive_entity'];
      $userID     = Session::getLoginUserID();
      $templates  = PluginUninstallUninstall::getAllTemplatesByEntity($_SESSION["glpiactive_entity"],
                                                                      true);
      $data       = plugin_version_uninstall();

      echo "<form action='" . $this->getFormURL() . "' method='post'>";
      echo "<div class='center'>";

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='2'>" . $data['name'] . "</th></tr>";

      if (!empty($templates)) {
         echo "<tr class='tab_bg_1 center'>";
         echo "<th>" . PluginUninstallModel::getTypeName() ."</th>";
         echo "<th>" . __("Item's location after uninstall", "uninstall") . "</th>";
         echo "</tr>";

         foreach ($templates as $ID => $name) {
            $pref_ID = self::checkIfPreferenceExistsByEntity($userID, $ID, $entity);

            if (!$pref_ID) {
               $pref_ID = self::addDefaultPreference($userID, $ID, $entity);
            }

            $this->getFromDB($pref_ID);

            echo "<tr class='tab_bg_1'><td>" . $name . "</td>";
            echo "<td>";
            $value = (isset($this->fields["locations_id"]) ? $this->fields["locations_id"] : 0);

            Location::dropdown(['name'      => "id[$pref_ID][locations_id]",
                                'value'     => ($value == '' ? 0 : $value),
                                'comments'  => 1,
                                'entity'    => $entity,
                                'toadd'     => [-1 => __('Keep previous location', 'uninstall'),
                                                0  => __('Empty location', 'uninstall')]]);

            echo "<input type='hidden' name='id[".$pref_ID."][id]' value='" . $pref_ID . "'>";
            echo "</td></tr>";
         }

         echo "<tr class='tab_bg_1'><td colspan='2' class='center'>";
         echo "<input type='submit' name='update_user_preferences_uninstall' value='" .
                _sx('button', 'Post'). "' class='submit'>";
         echo "</td></tr>";

      }

      echo "</table>";
      echo "</div>";
      Html::closeForm();
   }


   /**
    * @param $item
   **/
   static function afterUpdateModel($item) {

      if ($item->fields["is_recursive"] == 0) {
         self::deleteUserPreferenceForModel($item->fields["id"], $item->fields["entities_id"]);
      }
   }


   /**
    * @param $item
   **/
   static function beforeItemPurge($item) {

      switch ($item->getType()) {
         case 'User' :
            self::deleteUserPreferences($item->fields["id"]);
            break;

         case 'PluginUninstallModel' :
            self::deleteUserPreferenceForModel($item->fields["id"]);
            break;
      }
   }


   /**
    * @param $models_id
    * @param $except_entity   (default -1)
   **/
   static function deleteUserPreferenceForModel($models_id, $except_entity = -1) {
      global $DB;

      $query = "DELETE FROM `".getTableForItemType(__CLASS__)."`
              WHERE `templates_id` = '".$models_id."'";

      if ($except_entity != -1) {
         $query .= " AND `entities_id` NOT IN (".$except_entity.")";
      }
      $DB->query($query);
   }


   /**
    * @param $users_id
   **/
   static function deleteUserPreferences($users_id) {
      $preference = new self();
      $preference->deleteByCriteria(['users_id' => $users_id]);
   }


   /**
    * @param $user_id
    * @param $template
    * @param $entity
   **/
   static function checkIfPreferenceExistsByEntity($user_id, $template, $entity) {
      global $DB;

      $query = "SELECT `id`
                FROM `".getTableForItemType(__CLASS__)."`
                WHERE `users_id` = '" . $user_id . "'
                      AND `entities_id` = '" . $entity . "'
                      AND `templates_id` = '$template'";
      $result = $DB->query($query);

      if ($DB->numrows($result) > 0) {
         return $DB->result($result, 0, "id");
      }
      return 0;
   }


   /**
    * @param $user_id
    * @param $template
    * @param $entity
   **/
   static function addDefaultPreference($user_id, $template, $entity) {

      $pref                  = new self();
      $input["users_id"]     = $user_id;
      $input["entities_id"]  = $entity;
      $input["locations_id"] = -1;
      $input["templates_id"] = $template;
      return $pref->add($input);
   }


   /**
    * @param $user_id
    * @param $template
    * @param $entity
   **/
   static function getLocationByUserByEntity($user_id, $template, $entity) {
      global $DB;

      $query = "SELECT `locations_id`
                FROM `".getTableForItemType(__CLASS__)."`
                WHERE `users_id` = '" . $user_id . "'
                      AND `entities_id` = '" . $entity. "'
                      AND `templates_id` = '".$template."'";
      $result = $DB->query($query);

      if ($DB->numrows($result) > 0) {
         return $DB->result($result, 0, "locations_id");
      }
      return '';
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if ($item->getType() == 'Preference' && Session::haveRight('uninstall:profile', READ)) {
         return PluginUninstallUninstall::getTypeName();
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      global $CFG_GLPI;

      if ($item->getType() == 'Preference' && Session::haveRight('uninstall:profile', READ)) {
         $pref = new self();
         $pref->showFormUserPreferences();
      }
      return true;
   }


   static function install(Migration $migration) {
      global $DB;

      // From 0.2 to 1.0.0
      $table = 'glpi_plugin_uninstallcomputer_preference';
      if ($DB->tableExists($table)) {
         $migration->changeField($table, 'user_id', 'FK_users', "integer");
         $migration->addField($table, 'FK_template', 'integer');
         $migration->renameTable($table, getTableForItemType(__CLASS__));
      }

      $table = getTableForItemType(__CLASS__);
      // plugin already installed
      if ($DB->tableExists($table)) {
         // from 1.0.0 to 1.3.0
         if ($DB->fieldExists($table, 'ID')) {
            $migration->changeField($table, 'ID', 'id', 'autoincrement');
            $migration->changeField($table, 'FK_users', 'users_id', 'integer');
            $migration->changeField($table, 'FK_entities', 'entities_id', 'integer');
            $migration->changeField($table, 'FK_template', 'templates_id', 'integer');
            $migration->changeField($table, 'location', 'locations_id', "integer");
         }

      } else {
         // plugin nevers installed
         $query = "CREATE TABLE `".$table."` (
                     `id` int(11) NOT NULL AUTO_INCREMENT,
                     `users_id` int(11) NOT NULL,
                     `entities_id` int(11) DEFAULT '0',
                     `templates_id` int(11) DEFAULT '0',
                     `locations_id` int(11) DEFAULT '0',
                     PRIMARY KEY (`id`)
                     ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->queryOrDie($query, $DB->error());
      }
      return true;
   }


   static function uninstall() {
      $GLOBALS['DB']->query("DROP TABLE IF EXISTS `".getTableForItemType(__CLASS__)."`");
   }

}
