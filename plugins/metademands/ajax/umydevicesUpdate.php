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
if (strpos($_SERVER['PHP_SELF'], "umydevicesUpdate.php")) {
   include('../../../inc/includes.php');
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

Session::checkLoginUser();
$fieldUser = new PluginMetademandsField();
if (isset($_POST['id_fielduser']) && $_POST["id_fielduser"] > 0) {
   if (!isset($_POST['field'])) {
      if ($fieldUser->getFromDBByCrit(['link_to_user' => $_POST['id_fielduser'],
                                       'type'         => "dropdown_meta",
                                       'item'         => "mydevices"])) {
         $_POST["field"] = "field[" . $fieldUser->fields['id'] . "]";
      }

   } else {
      if (isset($_SESSION['plugin_metademands']['fields'][$_POST['id_fielduser']])) {
         $_POST['value'] = $_SESSION['plugin_metademands']['fields'][$_POST['id_fielduser']];
      }
   }
}

$users_id = 0;

$user = new User();
if (isset($_POST['value']) && $_POST["value"] > 0) {
   if ($user->getFromDB($_POST["value"])) {
      $users_id = $_POST['value'];
   }
}

if (isset($_POST['fields_id'])
    && isset($_SESSION['plugin_metademands']['fields'][$_POST['fields_id']])) {
   $users_id = $_SESSION['plugin_metademands']['fields'][$_POST['fields_id']];
}

$rand = mt_rand();
$p    = ['rand' => $rand,
         'name' => $_POST["field"]];
PluginMetademandsField::dropdownMyDevices($users_id, $_SESSION['glpiactiveentities'], 0, 0, $p);

$_POST['name'] = "mydevices_user";
$_POST['rand'] = "";
Ajax::commonDropdownUpdateItem($_POST);
