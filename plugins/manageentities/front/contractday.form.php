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

if (!isset($_GET["id"])) $_GET["id"] = "";
if (!isset($_GET["contract_id"])) $_GET["contract_id"] = 0;
if (!isset($_GET["showFromPlugin"])) $_GET["showFromPlugin"] = 0;

$contractday = new PluginManageentitiesContractDay();

if (isset($_POST["add"])) {
   $contractday->check(-1, UPDATE);
   $contractday->add($_POST);
   Html::back();

} else if (isset($_POST["update"])) {
   $contractday->check($_POST["id"], UPDATE);
   $contractday->update($_POST);
   Html::back();

} else if (isset($_POST["delete"])) {
   $contracts_id = $_POST["contracts_id"];
   $contractday->check($_POST["id"], UPDATE);
   $contractday->delete($_POST);
   Html::redirect(Toolbox::getItemTypeFormURL('Contract') . "?id=" . $contracts_id);

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

} else if (isset($_POST["deleteAll"])) {
   foreach ($_POST["item"] as $key => $val) {
      $input = ['id' => $key];
      if ($val == 1) {
         $contractday->check($key, UPDATE);
         $contractday->delete($input);
      }
   }
   Html::back();

} else {
   Html::header(PluginManageentitiesContractDay::getTypeName(2), '', "management", "pluginmanageentitiesentity", "contractday");
   if (Session::haveRight("contract", READ)) {
      $contractday->display($_GET);
   }
   Html::footer();
}