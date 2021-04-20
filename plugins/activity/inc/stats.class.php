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

class PluginActivityStats extends CommonDBTM {

   static $rightname = "plugin_activity";

   static function getTypeName($nb = 0) {
      return __('Activity statistics', 'activity');
   }

   /**
    * Get all reports from parsing class
    *
    * @params
   */

   function getTitleStats() {

      $stats = [];

      $classnames = self::listStatClasses();
      foreach ($classnames as $classname) {
         $stats[] = $classname::getTitle();
      }

      return $stats;
   }

   /**
    * Get all reports from parsing front
    *
    * @params
   */

   function getAllStats($with_url = true) {

      $stats = [];
      foreach ($this->getTitleStats() as $key => $title) {
         $stats[$key]['funct'][$key]['title'] = $title;
         $stats[$key]['funct'][$key]['id'] = md5($title);
      }

      return $stats;
   }

   function showStats($post, $get) {

      $classnames = self::listStatClasses();

      foreach ($classnames as $classname) {
         if (md5($classname::getTitle()) == $get['stat_id']) {
            $stats = new $classname();
            break;
         }
      }

      $params = $stats->initParams($post);

      Stat::title();

      $data = $stats->getData($params);
      $stats->showLine($params, $data);
   }

   public static function listStatClasses() {
      $classnames = [];

      foreach (glob(GLPI_ROOT . '/plugins/activity/inc/*.class.php') as $file) {

         $clean = str_replace(".class.php", "", basename($file));

         $classname = 'PluginActivity'.ucfirst($clean);
         if (class_exists($classname)) {
            $implemented_class = class_implements($classname);
            if (isset($implemented_class['PluginActivityInterface'])) {
               $classnames[] = $classname;
            }
         }
      }
      return $classnames;
   }
}
