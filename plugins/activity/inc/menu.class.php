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

class PluginActivityMenu extends CommonGLPI {
   static $rightname = 'plugin_activity';

   static function getMenuName() {
      return _n('Activity', 'Activities', 2, 'activity');
   }

   static function getMenuContent() {
      $plugin_page              = "/plugins/activity/front/menu.php";
      $menu                     = [];
      //Menu entry in tools
      $menu['title']            = self::getMenuName();
      $menu['page']             = $plugin_page;
      $menu['links']['search']  = $plugin_page;

      if (Session::haveRight(static::$rightname, UPDATE)
            || Session::haveRight("config", UPDATE)) {
         //Entry icon in breadcrumb
         $menu['links']['config']                      = PluginActivityConfig::getFormURL(false);
         //Link to config page in admin plugins list
         $menu['config_page']                          = PluginActivityConfig::getFormURL(false);

         $menu['options']['holidaycount']['title']           = _n('Holiday counter', 'Holiday counters', 2, 'activity');;
         $menu['options']['holidaycount']['page']            = '/plugins/activity/front/holidaycount.php';
         $menu['options']['holidaycount']['links']['add']    = '/plugins/activity/front/holidaycount.form.php';
         $menu['options']['holidaycount']['links']['search'] = '/plugins/activity/front/holidaycount.php';
         $menu['icon']                                       = self::getIcon();
      }

      return $menu;
   }

   static function getIcon() {
      return "far fa-calendar-alt";
   }

   static function removeRightsFromSession() {
      if (isset($_SESSION['glpimenu']['tools']['types']['PluginActivityMenu'])) {
         unset($_SESSION['glpimenu']['tools']['types']['PluginActivityMenu']);
      }
      if (isset($_SESSION['glpimenu']['tools']['content']['pluginactivitymenu'])) {
         unset($_SESSION['glpimenu']['tools']['content']['pluginactivitymenu']);
      }
   }
}