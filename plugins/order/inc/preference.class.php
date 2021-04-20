<?php
/*
 * @version $Id: HEADER 2011-03-23 15:41:26 tsmr $
 LICENSE

 This file is part of the order plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with Order. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   order
 @author    the order plugin team
 @copyright Copyright (c) 2010-2011 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/order
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginOrderPreference extends CommonDBTM {


   public static function checkIfPreferenceExists($users_id) {
      return self::checkPreferenceValue('id', $users_id);
   }


   public function addDefaultPreference($users_id) {
      $id = self::checkIfPreferenceExists($users_id);
      if (!$id) {
         $input["users_id"] = $users_id;
         $input["template"] = "";
         $input["sign"]     = "";
         $id = $this->add($input);
      }
      return $id;
   }


   /**
    *
    * Get a preference for an user
    * @since 1.5.3
    * @param unknown_type preference field to get
    * @param unknown_type user ID
    * @return preference value or 0
    */
   public static function checkPreferenceValue($field, $users_id = 0) {
      $data = getAllDataFromTable(self::getTable(), ['users_id' => $users_id]);
      if (!empty($data)) {
         $first = array_pop($data);
         return $first[$field];
      } else {
         return 0;
      }
   }


   public static function checkPreferenceSignatureValue($users_id = 0) {
      return self::checkPreferenceValue('sign', $users_id);
   }


   public static function checkPreferenceTemplateValue($users_id) {
      return self::checkPreferenceValue('template', $users_id);
   }


   /**
    *
    * Display a dropdown of all ODT template files available
    * @since 1.5.3
    * @param $value default value
    */
   public static function dropdownFileTemplates($value = '') {
      return self::dropdownListFiles('template', PLUGIN_ORDER_TEMPLATE_EXTENSION,
                                     PLUGIN_ORDER_TEMPLATE_DIR, $value);
   }


   /**
    *
    * Display a dropdown of all PNG signatures files available
    * @since 1.5.3
    * @param $value default value
    */
   public static function dropdownFileSignatures($value = '', $empy_value = true) {
      return self::dropdownListFiles('sign', PLUGIN_ORDER_SIGNATURE_EXTENSION,
                                     PLUGIN_ORDER_SIGNATURE_DIR, $value);
   }


   /**
    *
    * Display a dropdown which contains all files of a certain type in a directory
    * @since 1.5.3
    * @param $name dropdown name
    * @param $extension list files of this extension only
    * @param $directory directory in which to look for files
    * @param $value default value
    */
   public static function dropdownListFiles($name, $extension, $directory, $value = '') {
      $files  = self::getFiles($directory, $extension);
      $values = [];
      if (empty($files)) {
         $values[0] = Dropdown::EMPTY_VALUE;
      }
      foreach ($files as $file) {
         $values[$file[0]] = $file[0];
      }
      return Dropdown::showFromArray($name, $values, ['value' => $value]);
   }


   /**
    *
    * Check if at least one template exists
    * @since 1.5.3
    * @return true if at least one template exists, false otherwise
    */
   public static function atLeastOneTemplateExists() {
      $files = self::getFiles(PLUGIN_ORDER_TEMPLATE_DIR, PLUGIN_ORDER_TEMPLATE_EXTENSION);
      return (!empty($files));
   }


   /**
    *
    * Check if at least one signature exists
    * @since 1.5.3
    * @return true if at least one signature exists, false otherwise
    */
   public static function atLeastOneSignatureExists() {
      $files = self::getFiles(PLUGIN_ORDER_SIGNATURE_DIR, PLUGIN_ORDER_SIGNATURE_EXTENSION);
      return (!empty($files));
   }


   public function showForm($ID) {
      global $CFG_GLPI;

      $version = plugin_version_order();
      $this->getFromDB($ID);

      echo "<form method='post' action='".Toolbox::getItemTypeFormURL(__CLASS__)."'><div align='center'>";
      echo "<table class='tab_cadre_fixe' cellpadding='5'>";
      echo "<tr><th colspan='2'>" . $version['name'] . " - ".$version['version']."</th></tr>";
      echo "<tr class='tab_bg_2'><td align='center'>".__("Use this model", "order")."</td>";
      echo "<td align='center'>";
      self::dropdownFileTemplates($this->fields["template"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'><td align='center'>" .__("Use this sign", "order") ."</td>";
      echo "<td align='center'>";
      self::dropdownFileSignatures($this->fields["sign"]);
      echo "</td></tr>";

      if (isset($this->fields["sign"]) && !empty($this->fields["sign"])) {
         echo "<tr class='tab_bg_2'><td align='center' colspan='2'>";
          echo "<img src='".Plugin::getWebDir('order')."/front/signature.php?sign=".rawurlencode($this->fields["sign"])."'>";
         echo "</td></tr>";
      }

      echo "<tr class='tab_bg_2'><td align='center' colspan='2'>";
      echo Html::hidden('id', ['value' => $ID]);
      echo Html::hidden('users_id', ['value' => $this->fields['users_id']]);
      echo "<input type='submit' name='update' value='"._sx('button', 'Post')."' class='submit' ></td>";
      echo "</tr>";

      echo "</table>";
      echo "</div>";
      Html::closeForm();
   }


   public static function getFiles($directory, $ext) {
      $array_dir  = [];
      $array_file = [];

      if (is_dir($directory)) {
         if ($dh = opendir($directory)) {
            while (($file = readdir($dh)) !== false) {
               $filename  = $file;
               $filetype  = filetype($directory. $file);
               $filedate  = Html::convDate(date ("Y-m-d", filemtime($directory.$file)));
               $basename  = explode('.', basename($filename));
               $extension = array_pop($basename);
               if ($filename == ".." OR $filename == ".") {
                  echo "";
               } else {
                  if ($filetype == 'file' && $extension == $ext) {
                     if ($ext == PLUGIN_ORDER_SIGNATURE_EXTENSION) {
                        $name = array_shift($basename);
                        if (strtolower($name) == strtolower($_SESSION["glpiname"])) {
                           $array_file[] = [$filename, $filedate, $extension];
                        }
                     } else {
                        $array_file[] = [$filename, $filedate, $extension];
                     }

                  } else if ($filetype == "dir") {
                     $array_dir[] = $filename;
                  }
               }
            }
            closedir($dh);
         }
      }

      rsort($array_file);

      return $array_file;
   }


   public static function install(Migration $migration) {
      global $DB;

      //Only avaiable since 1.2.0
      $table = self::getTable();
      if (!$DB->tableExists($table)) {
         $migration->displayMessage("Installing $table");

         $query = "CREATE TABLE `$table` (
                  `id` int(11) NOT NULL auto_increment,
                  `users_id` int(11) NOT NULL default 0,
                  `template` varchar(255) collate utf8_unicode_ci default NULL,
                  `sign` varchar(255) collate utf8_unicode_ci default NULL,
                  PRIMARY KEY  (`id`),
                  KEY `users_id` (`users_id`)
               ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die ($DB->error());
      } else {

         //1.5.3
         $migration->changeField($table, 'ID', 'id', "int(11) NOT NULL auto_increment");
         $migration->changeField($table, 'user_id', 'users_id', "INT(11) NOT NULL DEFAULT '0'");
         $migration->addKey($table, 'users_id');
         $migration->migrationOneTable($table);
      }
   }


   public static function uninstall() {
      global $DB;

      //Current table name
      $DB->query("DROP TABLE IF EXISTS `".self::getTable()."`") or die ($DB->error());
   }


   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if (get_class($item) == 'Preference') {
         return [1 => __("Orders", "order")];
      }
      return '';
   }


   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if (get_class($item) == 'Preference') {
         $pref = new self();
         $id   = $pref->addDefaultPreference(Session::getLoginUserID());
         $pref->showForm($id);
      }
      return true;
   }


}
