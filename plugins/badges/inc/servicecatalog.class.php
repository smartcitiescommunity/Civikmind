<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 badges plugin for GLPI
 Copyright (C) 2009-2016 by the badges Development Team.

 https://github.com/InfotelGLPI/badges
 -------------------------------------------------------------------------

 LICENSE

 This file is part of badges.

 badges is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 badges is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with badges. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */


if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}


class PluginBadgesServicecatalog extends CommonGLPI {

   static $rightname = 'plugin_badges';

   var $dohistory = false;

   static function canUse() {
      return Session::haveRight(self::$rightname, READ);
   }

   /**
    * @return string
    */
   static function getMenuLink() {
      global $CFG_GLPI;

      return PLUGINBADGES_DIR . "/front/wizard.php";
   }

   static function getMenuLogo() {

      return "fas fa-id-badge";

   }

   static function getMenuTitle() {
      global $CFG_GLPI;

      return __('Manage temporary badges', 'badges');

   }


   static function getMenuComment() {

      return __('Manage temporary badges', 'badges');
   }

   static function getLinkList() {
      return "";
   }

   static function getList() {
      return "";
   }
}
