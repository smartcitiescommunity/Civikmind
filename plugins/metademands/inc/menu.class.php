<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Metademands plugin for GLPI
 Copyright (C) 2018-2019 by the Metademands Development Team.

 https://github.com/InfotelGLPI/metademands
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Metademands.

 Metademands is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Metademands is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Metademands. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/**
 * Class PluginMetademandsMenu
 */
class PluginMetademandsMenu extends CommonDBTM {

   static $rightname = 'plugin_metademands';

   /**
    * @return bool|int
    */
   static function canView() {
      return Session::haveRight(self::$rightname, READ);
   }

   /**
    * @return bool
    */
   static function canCreate() {
      return Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, DELETE]);
   }

   static function removeRightsFromSession() {
      if (isset($_SESSION['glpimenu']['helpdesk']['types']['PluginMetademandsMetademand'])) {
         unset($_SESSION['glpimenu']['helpdesk']['types']['PluginMetademandsMetademand']);
      }
      if (isset($_SESSION['glpimenu']['helpdesk']['content']['pluginmetademandsmetademand'])) {
         unset($_SESSION['glpimenu']['helpdesk']['content']['pluginmetademandsmetademand']);
      }
   }
}
