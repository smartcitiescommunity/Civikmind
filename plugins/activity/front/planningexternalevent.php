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

if (isset($_POST["action"])) {
   $_GET["action"] = $_POST["action"];
}

if (isset($_GET["users_id"])) {
   $users_id = $_GET["users_id"];

} else if (isset($_POST["users_id"])) {
   $users_id = $_POST["users_id"];

} else {
   $users_id = Session::getLoginUserID();
}

//TODO used by modal
//if (isset($_POST["action"])) {
//   $_GET["action"] = $_POST["action"];
//}

//TODO used by modal
//if (isset($_GET["action"])) {
//   Html::popHeader(PluginActivityActivity::getTypeName(2));
//} else {
if (Session::getCurrentInterface() == 'central') {
   Html::header(PluginActivityPlanningExternalEvent::getTypeName(2), '', "tools", "pluginactivitymenu");
} else {
   Html::helpHeader(PluginActivityPlanningExternalEvent::getTypeName(2));
}
//}

$activity = new PlanningExternalEvent();

if ($activity->canView()) {
   //TODO used by modal
   //if (((isset($_GET["action"]) && $_GET["action"] == "load")
   //      || (isset($_POST["action"]) && $_POST["action"] == "load"))
   //        && isset($users_id) && ($users_id > 0)) {

   //   $_GET['target']   = Toolbox::getItemTypeSearchURL("PluginActivityActivity");
   //   $_GET["users_id"] = $users_id;
   //   PluginActivityActivity::showGenericSearch(array_merge($_POST, $_GET));

   //} else {

      Search::show("PlanningExternalEvent");
   //}
} else {
   Html::displayRightError();
}

if (isset($_GET["action"])) {
   Html::popFooter();
} else {
   if (Session::getCurrentInterface() == 'central') {
      Html::footer();
   } else {
      Html::helpFooter();
   }
}