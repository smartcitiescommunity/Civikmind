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

include('../../../inc/includes.php');
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();
Session::checkLoginUser();

if (!isset($_POST["contracts_id"])) {
   exit();
}

if (isset($_POST["contracts_id"])) {
   $contract = new Contract();
   $contract->getEmpty();
   $contract->getFromDB($_POST["contracts_id"]);

   $contractdays_id = 0;
   if ($_POST["current_contracts_id"] == $_POST["contracts_id"]) {
      $contractdays_id = $_POST["contractdays_id"];
   }

   if ($contractdays_id == 0) {
      $contractday = new PluginManageentitiesContractDay();
      $restrict    = ['entities_id'  => $contract->fields['entities_id'],
                      'contracts_id' => $_POST["contracts_id"],
                      [
                         'OR' => [
                            ['plugin_manageentities_contractstates_id' => PluginManageentitiesContractState::getOpenedStates()],
                            ['id' => $contractdays_id]
                         ]
                      ]];
      $datas       = $contractday->find($restrict);
      //if a single contractday
      if (count($datas) == 1) {
         $datas = reset($datas);
         //Default contractday Display
         $contractdays_id = $datas['id'];
      }
   }
   if (isset($contract->fields['states_id']) && $contract->fields['states_id'] > 0) {
      echo __('Status') . " : " . Dropdown::getDropdownName("glpi_states", $contract->fields['states_id']);
      echo "<br><br>";
   }

   $restrict = ['entities_id'  => $contract->fields['entities_id'],
                'contracts_id' => $_POST["contracts_id"],
                [
                   'OR' => [
                      ['plugin_manageentities_contractstates_id' => PluginManageentitiesContractState::getOpenedStates()],
                      ['id' => $contractdays_id]
                   ]
                ]];

   Dropdown::show('PluginManageentitiesContractDay', ['name'      => 'plugin_manageentities_contractdays_id',
                                                      'value'     => $contractdays_id,
                                                      'condition' => $restrict,
                                                      'width'     => $_POST['width']]);
}