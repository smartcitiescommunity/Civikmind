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

if (Session::haveRight("plugin_manageentities", UPDATE)) {
   $criprice = new PluginManageentitiesCriPrice();

   if (isset($_POST["add"])) {
      Session::checkRight("contract", CREATE);
      $criprice->add($_POST);

      Html::back();

   } elseif (isset($_POST["update"])) {
      $criprice->check($_POST["id"], UPDATE);
      $criprice->update($_POST);

      Html::back();

   } elseif (isset($_POST["delete"])) {
      Session::checkRight("contract", DELETE);
      $criprice->delete($_POST, 1);

      Html::back();
   }

} else {
   Html::header(__('Setup'), '', "config", "plugins");
   echo "<div align='center'><br><br>";
   echo "<i class='fas fa-exclamation-triangle fa-4x' style='color:orange'></i><br><br>";
   echo "<b>" . __("You don't have permission to perform this action.") . "</b></div>";
   Html::footer();
}