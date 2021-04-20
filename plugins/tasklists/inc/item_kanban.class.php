<?php

/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2018 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

class PluginTasklistsItem_Kanban extends CommonDBRelation {

   static public $itemtype_1         = 'itemtype';
   static public $items_id_1         = 'items_id';
   static public $itemtype_2         = 'User';
   static public $items_id_2         = 'users_id';
   static public $checkItem_1_Rights = 'plugin_tasklists';


   /**
    * Load the state of a Kanban's column for a specific kanban for the current user
    *
    * @param string $itemtype Type of the item.
    * @param int    $items_id ID of the item.
    * @param int    $plugin_tasklists_taskstates_id column id
    * @param string $timestamp Timestamp string of last check or null to always get the state.
    *
    * @return return the state of the collummn for the user
    *          if the state doesn't exist it is created
    * @since 9.5.0
    */
   static function loadStateForItem($itemtype, $items_id, $plugin_tasklists_taskstates_id, $timestamp = null) {
      global $DB;


      $item = new self();
      if ($item->getFromDBByCrit([
                                    'users_id'                       => Session::getLoginUserID(),
                                    'itemtype'                       => $itemtype,
                                    'items_id'                       => $items_id,
                                    'plugin_tasklists_taskstates_id' => $plugin_tasklists_taskstates_id
                                 ])) {
         return $item->getField('state');

      } else {
         $input = [
            'users_id'                       => Session::getLoginUserID(),
            'itemtype'                       => $itemtype,
            'items_id'                       => $items_id,
            'state'                          => false,
            'plugin_tasklists_taskstates_id' => $plugin_tasklists_taskstates_id,
            'date_creation'                  => $_SESSION['glpi_currenttime'],
            'date_mod'                       => $_SESSION['glpi_currenttime']
         ];
         $item->add($input);
         return false;
      }
   }


   static function collapseColumn($itemtype, $items_id, $column) {
      $item = new self();
      $item->getFromDBByCrit([
                                'users_id'                       => Session::getLoginUserID(),
                                'itemtype'                       => $itemtype,
                                'items_id'                       => $items_id,
                                'plugin_tasklists_taskstates_id' => $column
                             ]);
      $input             = $item->fields;
      $input["state"]    = true;
      $input["date_mod"] = $_SESSION['glpi_currenttime'];
      $item->update($input);
   }

   static function expandColumn($itemtype, $items_id, $column) {

      $item = new self();
      $item->getFromDBByCrit([
                                'users_id'                       => Session::getLoginUserID(),
                                'itemtype'                       => $itemtype,
                                'items_id'                       => $items_id,
                                'plugin_tasklists_taskstates_id' => $column
                             ]);
      $input             = $item->fields;
      $input["state"]    = false;
      $input["date_mod"] = $_SESSION['glpi_currenttime'];
      $item->update($input);
   }
}