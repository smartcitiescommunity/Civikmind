<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Metademands plugin for GLPI
 Copyright (C) 2018-2019 by the Metademands Development Team.

 https://github.com/InfotelGLPI/metademands
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Metademands.

 Metademands is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Metademands is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Metademands. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

$AJAX_INCLUDE = 1;
if (strpos($_SERVER['PHP_SELF'], "utooltipUpdate.php")) {
   include('../../../inc/includes.php');
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

Session::checkLoginUser();

if (isset($_SESSION['plugin_metademands']['fields'][$_POST['id_fielduser']])) {
   $_POST['value'] = $_SESSION['plugin_metademands']['fields'][$_POST['id_fielduser']];
}

$user = new User();
if (isset($_POST['value']) && $_POST["value"] > 0) {
   if ($user->getFromDB($_POST["value"])) {
      PluginMetademandsWizard::showUserInformations($user);
   }
}

$_POST['name'] = "tooltip_user";
$_POST['rand'] = "";
Ajax::commonDropdownUpdateItem($_POST);
