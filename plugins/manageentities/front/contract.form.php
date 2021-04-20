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

$contractday = new PluginManageentitiesContractDay();
$contract    = new PluginManageentitiesContract();

if (isset($_POST["addcontract"])) {
   $contract->check(-1, UPDATE);
   $newID = $contract->add($_POST);
   Html::back();

} else if (isset($_POST["delcontract"])) {
   $contract->check($_POST["id"], UPDATE);
   $contract->delete($_POST);
   Html::back();

} else if (isset($_POST["updatecontract"])) {
   $contract->check($_POST["id"], UPDATE);
   $contract->update($_POST);
   Html::back();

} else if (isset($_POST["add_nbday"]) && isset($_POST['nbday'])) {
   Session::checkRight("contract", UPDATE);
   $contractday->addNbDay($_POST);
   Html::back();

} else if (isset($_POST["delete_nbday"])) {
   Session::checkRight("contract", UPDATE);
   foreach ($_POST["item_nbday"] as $key => $val) {
      if ($val == 1) {
         $contractday->delete(['id' => $key]);
      }
   }
   Html::back();

} else {
   $contractday->checkGlobal(READ);

   Html::header(PluginManageentitiesContractDay::getTypeName(2), '', "management", "pluginmanageentitiesentity", "contractday");
   $contractday->display($_GET);

   Html::footer();
}