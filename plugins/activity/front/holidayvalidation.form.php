<?php

/*
 -------------------------------------------------------------------------
 Activity plugin for GLPI
 Copyright (C) 2019 by the Activity Development Team.
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Activity.

 Activity is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Activity is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Activity. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

include ('../../../inc/includes.php');

Session::checkLoginUser();

if (!isset($_GET["id"]) && !(isset($_POST['id']))) {
   Html::displayErrorAndDie(__('Item not found'));
} else {
   if (isset($_GET['id'])) {
      $ID = $_GET["id"];
   }
   if (isset($_POST['id'])) {
      $ID = $_POST["id"];
   }
}

$holidayValidation = new PluginActivityHolidayValidation();

if (isset($_POST["add"])) {

   $holidayValidation->check(-1, CREATE, $_POST);
   $holidayValidation->add($_POST);

   Html::back();

} else if (isset($_POST["update"])) {
   $holidayValidation->check($_POST['id'], UPDATE);
   $holidayValidation->update($_POST);
   Html::back();

} else if (isset($_POST["delete"])) {
   $holidayValidation->check($_POST['id'], PURGE);
   $holidayValidation->delete($_POST);
   Html::back();

} else {
   $holidayValidation->checkGlobal(READ);

   if (Session::getCurrentInterface() == 'central') {
      Html::header(PluginActivityHolidayValidation::getTypeName(2), '', "tools", "pluginactivitymenu");
   } else {
      Html::helpHeader(PluginActivityHolidayValidation::getTypeName(2));
   }

   $holidayValidation->display(['id' => $_GET['id']]);
   if (Session::getCurrentInterface() == 'central') {
      Html::footer();
   } else {
      Html::helpFooter();
   }

}