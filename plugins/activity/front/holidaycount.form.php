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

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}
if (!isset($_GET["users_id"])) {
   $users_id = Session::getLoginUserID();
} else {
   $users_id = $_GET["users_id"];
}

$count = new PluginActivityHolidaycount();

if (isset($_POST["add"])) {

   $count->check(-1, CREATE, $_POST);
   $count->add($_POST);
   Html::back();

} else if (isset($_POST["update"])) {
   $count->check($_POST['id'], UPDATE);
   $count->update($_POST);
   Html::back();

} else if (isset($_POST["purge"])) {
   $count->check($_POST['id'], PURGE);
   $count->delete($_POST);
   $count->redirectToList();

} else {
   $count->checkGlobal(READ);
   if (!isset($_GET['_in_modal'])) {
      if (Session::getCurrentInterface() == 'central') {
         Html::header(PluginActivityHolidaycount::getTypeName(2), '', "tools", "pluginactivitymenu", "holidaycount");
      } else {
         Html::helpHeader(PluginActivityHolidaycount::getTypeName(2));
      }
   } else {
      Html::popHeader(PluginActivityHolidaycount::getTypeName(2));
   }

   $count->display($_GET);
   if (!isset($_GET['_in_modal'])) {
      if (Session::getCurrentInterface() == 'central') {
         Html::footer();
      } else {
         Html::helpFooter();
      }
   } else {
      Html::popFooter();
   }
}