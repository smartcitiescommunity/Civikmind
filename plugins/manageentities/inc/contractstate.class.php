<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Manageentities plugin for GLPI
 Copyright (C) 2014-2017 by the Manageentities Development Team.

 https://github.com/InfotelGLPI/manageentities
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Manageentities.

 Manageentities is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Manageentities is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Manageentities. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginManageentitiesContractState extends CommonDropdown {

   static $rightname = 'plugin_manageentities';

   static function getTypeName($nb = 0) {
      return _n('State of contract', 'States of contracts', $nb, 'manageentities');
   }

   static function canView() {
      return Session::haveRight(self::$rightname, READ);
   }

   static function canCreate() {
      return Session::HaveRightsOr(self::$rightname, [CREATE, UPDATE, DELETE]);
   }

   function getAdditionalFields() {
      return [['name'  => 'is_active',
               'label' => __('Active'),
               'type'  => 'bool'],
              ['name'  => 'is_closed',
               'label' => __('Closed'),
               'type'  => 'bool'],
              ['name'  => 'color',
               'label' => __('Color', 'manageentities'),
               'type'  => 'text'],
      ];
   }

   function rawSearchOptions() {
      $tab = parent::rawSearchOptions();

      $tab[] = [
         'id'       => '14',
         'table'    => $this->getTable(),
         'field'    => 'is_active',
         'name'     => __('Active'),
         'datatype' => 'bool'
      ];

      $tab[] = [
         'id'       => '15',
         'table'    => $this->getTable(),
         'field'    => 'is_closed',
         'name'     => __('Closed'),
         'datatype' => 'bool'
      ];

      $tab[] = [
         'id'       => '17',
         'table'    => $this->getTable(),
         'field'    => 'color',
         'name'     => __('Color', 'manageentities'),
         'datatype' => 'bool'
      ];

      return $tab;
   }

   public function prepareInputForAdd($input) {
      return $this->checkColor($input);
   }

   public function prepareInputForUpdate($input) {
      return $this->checkColor($input);
   }

   function checkColor($input) {
      if (!preg_match('/#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?\b/', $input['color'])) {
         Session::addMessageAfterRedirect(__('Color field is not correct', 'manageentities'), true, ERROR);
         return [];
      }
      return $input;
   }

   static function getOpenedStates() {
      $out  = [];
      $dbu  = new DbUtils();
      $data = $dbu->getAllDataFromTable('glpi_plugin_manageentities_contractstates', ["`is_active`" => 1]);
      if (!empty($data)) {
         foreach ($data as $val) {
            $out[] = $val['id'];
         }
      }

      return $out;
   }
}