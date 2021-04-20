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

if (isset($_GET['itemtype'])) {
   unset($_GET['root_doc']);

   $_SESSION['glpisearch'][] = $_GET;
   $_SESSION['glpisearch'][$_GET['itemtype']] = $_GET;
   $_SESSION['glpisearchcount'] = [$_GET['itemtype'] => count($_GET['criteria'])];

   $target = Toolbox::getItemTypeSearchURL($_GET['itemtype']);

   header('Location: '.$target."?".Toolbox::append_params($_GET, '&'));

} else {
   if (empty($_POST["month"])) {
      $_POST["month"] = intval(strftime("%m"));
   }

   if (empty($_POST["year"])) {
      $_POST["year"] = intval(strftime("%Y"));
   }

   if (!isset($_POST["users_id"])
           || empty($_POST["users_id"])) {
      $_POST["users_id"] = Session::getLoginUserID();
   }

   $report = new PluginActivityReport();

   $report->showGenericSearch($_POST);
}