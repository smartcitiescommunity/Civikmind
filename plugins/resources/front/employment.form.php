<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 resources plugin for GLPI
 Copyright (C) 2009-2016 by the resources Development Team.

 https://github.com/InfotelGLPI/resources
 -------------------------------------------------------------------------

 LICENSE

 This file is part of resources.

 resources is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 resources is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with resources. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}
if (!isset($_GET["plugin_resources_resources_id"])) {
   $_GET["plugin_resources_resources_id"] = 0;
}

$employment = new PluginResourcesEmployment();

if (isset($_POST["add"])) {
   $employment->check(-1, UPDATE);
   $newID = $employment->add($_POST);
   Html::back();

} else if (isset($_POST["update"])) {
   $employment->check($_POST["id"], UPDATE);
   $employment->update($_POST);
   Html::back();

} else if (isset($_POST["delete"])) {
   $employment->check($_POST["id"], UPDATE);
   $employment->delete($_POST);
   $employment->redirectToList();

} else if (isset($_POST["purge"])) {
   $employment->check($_POST['id'], UPDATE);
   $employment->delete($_POST, 1);
   $employment->redirectToList();

} else if (isset($_POST["restore"])) {
   $employment->check($_POST["id"], UPDATE);
   $employment->restore($_POST);
   $employment->redirectToList();

} else if (isset($_POST["add_item"])) {
   if (!empty($_POST['itemtype'])) {
      $input['id'] = $_POST['plugin_resources_employments_id'];
      $input['plugin_resources_resources_id'] = $_POST['items_id'];

      $employment->check($input["id"], UPDATE);
      $employment->update($input);
   }
   Html::back();

} else {
   $employment->checkGlobal(READ);
   Html::header(PluginResourcesResource::getTypeName(2), '', "admin", PluginResourcesMenu::getType(), strtolower(PluginResourcesEmployment::getType()));
   $employment->display(['id' => $_GET["id"], 'plugin_resources_resources_id' => $_GET["plugin_resources_resources_id"]]);
   Html::footer();
}
