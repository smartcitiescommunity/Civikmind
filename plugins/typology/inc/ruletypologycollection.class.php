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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginTypologyRuleTypologyCollection
 */
class PluginTypologyRuleTypologyCollection extends RuleCollection {

   // From RuleCollection
   public $stop_on_first_match=true;
   static $rightname = 'plugin_typology';
   public $menu_option='typologies';

   /**
    * Get title used in list of rules
    *
    * @return Title of the rule collection
    **/
   function getTitle() {

      return __('Rules for assigning a typology to a computer', 'typology');
   }

   /**
    * PluginTypologyRuleTypologyCollection constructor.
    *
    * @param int $entity
    */
   function __construct($entity = 0) {
      $this->entity = $entity;
   }

   /**
    * @return bool
    */
   function showInheritedTab() {
      return Session::haveRight("plugin_typology", UPDATE)
               && ($this->entity);
   }

   /**
    * @return bool
    */
   function showChildrensTab() {
      return Session::haveRight("plugin_typology", UPDATE)
               && (count($_SESSION['glpiactiveentities']) > 1);
   }
}
