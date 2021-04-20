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

require("../fpdf/font/symbol.php");

Session::checkLoginUser();
if (!isset($_POST["cri"])) $_POST["cri"] = "";
if (!isset($_GET["action"])) $_GET["action"] = "";

Html::popHeader(__('Generation of the intervention report', 'manageentities'));

$PluginManageentitiesCri           = new PluginManageentitiesCri();
$PluginManageentitiesCriTechnician = new PluginManageentitiesCriTechnician();
$criDetail                         = new PluginManageentitiesCriDetail();

if (isset($_POST["addcridetail"])) {
   if ($PluginManageentitiesCri->canCreate()) {
      $criDetail->add($_POST);
   }
   if(strpos($_SERVER['HTTP_REFERER'],"generatecri.form.php") > 0){
      Html::redirect($CFG_GLPI['root_doc']."/plugins/manageentities/front/generatecri.form.php?download=1&tickets_id=".$_POST['tickets_id']);
   } else{
      Html::back();
   }

} else if (isset($_POST["updatecridetail"])) {
   if ($PluginManageentitiesCri->canCreate()) {
      if (isset($_POST['withcontract']) && !$_POST['withcontract']) {
         $_POST['contracts_id']                          = 0;
         $_POST['plugin_manageentities_contractdays_id'] = 0;
      }
      $criDetail->update($_POST);
   }
   Html::back();

} else if (isset($_POST["delcridetail"])) {
   if ($PluginManageentitiesCri->canCreate()) {
      $criDetail->delete($_POST);
   }
   Html::back();

} else if (isset($_POST["purgedoc"])) {
   $doc         = new Document();
   $input['id'] = $_POST['documents_id'];
   if ($doc->delete($input, 1)) {
      \Glpi\Event::log($input['id'], "documents", 4, "document", $_SESSION["glpiname"] . " " . __('Delete permanently'));
   }
   Html::back();

} else {
   $PluginManageentitiesCri->showForm($_GET["job"], ['action' => $_GET["action"]]);
}

Html::popFooter();