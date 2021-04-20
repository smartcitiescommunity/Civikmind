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

$company = new PluginManageentitiesCompany();

if (isset($_POST["add"])) {
   $company->check(-1, CREATE);
   $company->add($_POST);
   if ($_SESSION['glpibackcreated']) {
      Html::redirect($company->getFormURL() . "?id=" . $newID);
   }
   Html::back();
} else if (isset($_POST["update"])) {
   $company->check($_POST["id"], UPDATE);
   $company->update($_POST);
   Html::back();
} else if (isset($_POST["purge"])) {
   $company_id = $_POST["id"];
   $company->check($_POST["id"], PURGE);
   $company->delete($_POST, 1);
   $company->redirectToList();
} else {
   Html::header(PluginManageentitiesCompany::getTypeName(2), '', "management", "pluginmanageentitiesentity", "company");
   $company->display($_GET);
   Html::footer();
}