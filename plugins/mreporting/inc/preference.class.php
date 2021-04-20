<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Mreporting plugin for GLPI
 Copyright (C) 2003-2011 by the mreporting Development Team.

 https://forge.indepnet.net/projects/mreporting
 -------------------------------------------------------------------------

 LICENSE

 This file is part of mreporting.

 mreporting is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 mreporting is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with mreporting. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

class PluginMreportingPreference extends CommonDBTM {

   static function checkIfPreferenceExists($users_id) {
      return self::checkPreferenceValue('id', $users_id);
   }

   function addDefaultPreference($users_id) {
      $id = self::checkIfPreferenceExists($users_id);
      if (!$id) {
         $input["users_id"]  = $users_id;
         $input["template"]  = "";
         $input["selectors"] = null;

         $id = $this->add($input);
      }
      return $id;
   }

   /**
    *
    * Get a preference for an user
    * @param unknown_type preference field to get
    * @param unknown_type user ID
    * @return preference value or 0
    */
   static function checkPreferenceValue($field, $users_id = 0) {
      $data = getAllDataFromTable(getTableForItemType(__CLASS__), ['users_id' => $users_id]);
      if (!empty($data)) {
         $first = array_pop($data);
         return $first[$field];
      } else {
         return 0;
      }
   }

   static function checkPreferenceTemplateValue($users_id) {
      return self::checkPreferenceValue('template', $users_id);
   }

   /**
    *
    * Display a dropdown of all ODT template files available
    * @param $value default value
    */
   static function dropdownFileTemplates($value = '') {
      return self::dropdownListFiles('template', PLUGIN_MREPORTING_TEMPLATE_EXTENSION,
                                     PLUGIN_MREPORTING_TEMPLATE_DIR, $value);
   }


   /**
    *
    * Display a dropdown which contains all files of a certain type in a directory
    * @param $name dropdown name
    * @param $extension list files of this extension only
    * @param $directory directory in which to look for files
    * @param $value default value
    */
   static function dropdownListFiles($name, $extension, $directory, $value = '') {
      $files     = self::getFiles($directory, $extension);
      $values    = [];
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
    * @return true if at least one template exists, false otherwise
    */
   static function atLeastOneTemplateExists() {
      $files = self::getFiles(PLUGIN_MREPORTING_TEMPLATE_DIR, PLUGIN_MREPORTING_TEMPLATE_EXTENSION);
      return (!empty($files));
   }

   function showForm($ID) {
      $this->getFromDB($ID);

      $version = plugin_version_mreporting();

      echo "<form method='post' action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";
      echo "<div align='center'>";

      echo "<table class='tab_cadre_fixe' cellpadding='5'>";

      echo "<tr><th colspan='2'>" . $version['name'] . " - ". $version['version'] . "</th></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td align='center'>";__("Please, select a model in your preferences", 'mreporting')."</td>";
      echo "<td align='center'>";
      self::dropdownFileTemplates($this->fields["template"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td align='center' colspan='2'>";
      echo "<input type='hidden' name='id' value='".$ID."'>";
      echo "<input type='hidden' name='users_id' value='".$this->fields['users_id']."'>";
      echo "<input type='submit' name='update' value='"._sx('button', 'Post')."' class='submit'>";
      echo "</td>";
      echo "</tr>";

      echo "</table>";
      echo "</div>";
      Html::closeForm();
   }

   static function getFiles($directory, $ext) {

      $array_dir  = [];
      $array_file = [];

      if (is_dir($directory)) {
         if ($dh = opendir($directory)) {
            while (($file = readdir($dh)) !== false) {
               $filename  = $file;
               $filetype  = filetype($directory. $file);
               $filedate  = Html::convDate(date ("Y-m-d", filemtime($directory . $file)));
               $basename  = explode('.', basename($filename));
               $extension = array_pop($basename);
               switch ($filename) {
                  case '..':
                  case '.':
                     echo "";
                  break;

                  default:
                     if ($filetype == 'file' && $extension ==$ext) {
                        $array_file[] = [$filename, $filedate, $extension];
                     } else if ($filetype == "dir") {
                        $array_dir[] = $filename;
                     }
                  break;
               }
            }
            closedir($dh);
         }
      }

      rsort($array_file);

      return $array_file;
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      switch (get_class($item)) {
         case 'Preference':
            return [1 => __("More Reporting", 'mreporting')];
         default:
            return '';
      }
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch (get_class($item)) {
         case 'Preference':
            $pref = new self();
            $id = $pref->addDefaultPreference(Session::getLoginUserID());
            $pref->showForm($id);
            break;
      }
      return true;
   }

}
