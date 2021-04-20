<?php

/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 databases plugin for GLPI
 Copyright (C) 2009-2016 by the databases Development Team.

 https://github.com/InfotelGLPI/databases
 -------------------------------------------------------------------------

 LICENSE

 This file is part of databases.

 databases is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 databases is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with databases. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/**
 * Class PluginDatabasesMenu
 */
class PluginDatabasesMenu extends CommonGLPI {
   static $rightname = 'plugin_databases';

   /**
    * @return translated
    */
   static function getMenuName() {
      return _n('Database', 'Databases', 2, 'databases');
   }

   /**
    * @return array
    */
   static function getMenuContent() {

      $menu                    = [];
      $menu['title']           = self::getMenuName();
      $menu['page']            = PLUGIN_DATABASES_DIR_NOFULL."/front/database.php";
      $menu['links']['search'] = PluginDatabasesDatabase::getSearchURL(false);
      if (PluginDatabasesDatabase::canCreate()) {
         $menu['links']['add'] = PluginDatabasesDatabase::getFormURL(false);
      }
      $menu['icon'] = self::getIcon();

      return $menu;
   }

   static function getIcon() {
      return "fas fa-database";
   }

   static function removeRightsFromSession() {
      if (isset($_SESSION['glpimenu']['assets']['types']['PluginDatabasesMenu'])) {
         unset($_SESSION['glpimenu']['assets']['types']['PluginDatabasesMenu']);
      }
      if (isset($_SESSION['glpimenu']['assets']['content']['plugindatabasesmenu'])) {
         unset($_SESSION['glpimenu']['assets']['content']['plugindatabasesmenu']);
      }
   }
}
