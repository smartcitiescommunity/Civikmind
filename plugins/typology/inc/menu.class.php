<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 typology plugin for GLPI
 Copyright (C) 2009-2016 by the typology Development Team.

 https://github.com/InfotelGLPI/typology
 -------------------------------------------------------------------------

 LICENSE

 This file is part of typology.

 typology is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 typology is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with typology. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/**
 * Class PluginTypologyMenu
 */
class PluginTypologyMenu extends CommonGLPI {
   static $rightname = 'plugin_typology';

   /**
    * Get menu name
    *
    * @since 0.85
    *
    * @return string character menu shortcut key
    **/
   static function getMenuName() {
      return _n('Typology', 'Typologies', 2, 'typology');
   }

   /**
    * get menu content
    *
    * @since 0.85
    *
    * @return array array for menu
    **/
   static function getMenuContent() {

      $menu                                           = [];
      $menu['title']                                  = self::getMenuName();
      $menu['page']                                   = PLUGIN_TYPOLOGY_DIR_NOFULL . "/front/typology.php";
      $menu['links']['search']                        = PluginTypologyTypology::getSearchURL(false);
      $menu['icon']                                   = self::getIcon();
      if (PluginTypologyTypology::canCreate()) {
         $menu['links']['add']                        = PluginTypologyTypology::getFormURL(false);
      }

      return $menu;
   }

   static function getIcon() {
      return "fab fa-typo3";
   }

   static function removeRightsFromSession() {
      if (isset($_SESSION['glpimenu']['tools']['types']['PluginTypologyMenu'])) {
         unset($_SESSION['glpimenu']['tools']['types']['PluginTypologyMenu']);
      }
      if (isset($_SESSION['glpimenu']['tools']['content']['plugintypologymenu'])) {
         unset($_SESSION['glpimenu']['tools']['content']['plugintypologymenu']);
      }
   }
}
