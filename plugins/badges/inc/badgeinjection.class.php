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

/**
 * Class PluginBadgesBadgeInjection
 */
class PluginBadgesBadgeInjection extends PluginBadgesBadge
   implements PluginDatainjectionInjectionInterface {

   /**
    * @return mixed
    */
   static function getTable($classname = null) {

      $parenttype = get_parent_class();
      return $parenttype::getTable();

   }

   /**
    * @return bool
    */
   function isPrimaryType() {
      return true;
   }

   /**
    * @return array
    */
   function connectedTo() {
      return [];
   }

   /**
    * @param string $primary_type
    *
    * @return array|the
    */
   function getOptions($primary_type = '') {

      $tab = Search::getOptions(get_parent_class($this));

      //Specific to location
      $tab[4]['checktype'] = 'date';
      $tab[5]['checktype'] = 'date';

      //$blacklist = PluginDatainjectionCommonInjectionLib::getBlacklistedOptions();
      //Remove some options because some fields cannot be imported
      $notimportable            = [11, 30, 80];
      $options['ignore_fields'] = $notimportable;
      $options['displaytype']   = ["dropdown"       => [2, 6, 7],
                                        "user"           => [10],
                                        "multiline_text" => [8],
                                        "date"           => [4, 5],
                                        "bool"           => [9]];

      $tab = PluginDatainjectionCommonInjectionLib::addToSearchOptions($tab, $options, $this);

      return $tab;
   }

   /**
    * Standard method to delete an object into glpi
    * WILL BE INTEGRATED INTO THE CORE IN 0.80
    *
    * @param array         $values
    * @param array|options $options
    *
    * @return an
    * @internal param fields $fields to add into glpi
    * @internal param options $options used during creation
    */
   function deleteObject($values = [], $options = []) {
      $lib = new PluginDatainjectionCommonInjectionLib($this, $values, $options);
      $lib->deleteObject();
      return $lib->getInjectionResults();
   }

   /**
    * Standard method to add an object into glpi
    * WILL BE INTEGRATED INTO THE CORE IN 0.80
    *
    * @param array|fields  $values
    * @param array|options $options
    *
    * @return an array of IDs of newly created objects : for example array(Computer=>1, Networkport=>10)
    * @internal param fields $values to add into glpi
    * @internal param options $options used during creation
    */
   function addOrUpdateObject($values = [], $options = []) {

      $lib = new PluginDatainjectionCommonInjectionLib($this, $values, $options);
      $lib->processAddOrUpdate();
      return $lib->getInjectionResults();
   }
}
