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

/**
 * Class PluginBadgesMenu
 */
class PluginBadgesMenu extends CommonGLPI {
   static $rightname = 'plugin_badges';

   /**
    * @return translated
    */
   static function getMenuName() {
      return _n('Badge', 'Badges', 2, 'badges');
   }

   /**
    * @return array
    */
   static function getMenuContent() {

      $menu                    = [];
      $menu['title']           = self::getMenuName();
      $menu['page']            = Plugin::getPhpDir('badges', false) . "/front/badge.php";
      $menu['links']['search'] = PluginBadgesBadge::getSearchURL(false);
      if (PluginBadgesBadge::canCreate()) {
         $menu['links']['add'] = PluginBadgesBadge::getFormURL(false);
      }

      $menu['icon']    = self::getIcon();

      return $menu;
   }

   /**
    * @return string
    */
   static function getIcon() {
      return "fas fa-id-badge";
   }

   static function removeRightsFromSession() {
      if (isset($_SESSION['glpimenu']['assets']['types']['PluginBadgesMenu'])) {
         unset($_SESSION['glpimenu']['assets']['types']['PluginBadgesMenu']);
      }
      if (isset($_SESSION['glpimenu']['assets']['content']['pluginbadgesmenu'])) {
         unset($_SESSION['glpimenu']['assets']['content']['pluginbadgesmenu']);
      }
   }
}
