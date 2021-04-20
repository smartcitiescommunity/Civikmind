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

if (isset($_GET["users_id"])) {
   $users_id = $_GET["users_id"];

} else if (isset($_POST["users_id"])) {
   $users_id = $_POST["users_id"];

} else {
   $users_id = Session::getLoginUserID();
}

if (!isset($_GET["id"])) {
   $_GET["id"] = 0;
}

if (isset($_GET["popup"])) {
   $_SESSION["glpipopup"]["name"] = $_GET["popup"];
}

if (isset($_SESSION["glpipopup"]["name"])) {
   switch ($_SESSION["glpipopup"]["name"]) {
      case "planningexternalevents" :
         Html::popHeader(PluginActivityPlanningExternalEvent::getTypeName(2), $_SERVER['PHP_SELF']);
         $_POST['target'] = "popup.php";
         PluginActivityPlanningExternalEvent::showGenericSearch(array_merge($_POST, ['users_id' => $users_id]));
         break;
      case "holiday" :
         $holiday = new PluginActivityHoliday();
         Html::popHeader(PluginActivityHoliday::getTypeName(2), $_SERVER['PHP_SELF']);
         $holiday->showForm($_GET["id"], ['users_id' => $users_id]);
         break;
   }

   echo "<div class='center'><br><a href='javascript:window.close()'>".__('Close')."</a>";
   echo "</div>";
   Html::popFooter();
}
