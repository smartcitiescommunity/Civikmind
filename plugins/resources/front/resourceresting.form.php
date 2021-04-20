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

if (Session::getCurrentInterface() == 'central') {
   //from central
   Html::header(PluginResourcesResource::getTypeName(2), '', "admin", PluginResourcesMenu::getType());
} else {
   //from helpdesk
   Html::helpHeader(PluginResourcesResource::getTypeName(2));
}

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}

$resting = new PluginResourcesResourceResting();

if (isset($_POST["addrestingresources"]) && $_POST["plugin_resources_resources_id"] != 0) {
   $resting->add($_POST);
   Html::back();

} else if (isset($_POST["updaterestingresources"]) && $_POST["plugin_resources_resources_id"] != 0) {
   $resting->update($_POST);
   Html::back();

} else if (isset($_POST["addenddaterestingresources"]) && isset($_POST["date_end"])) {
   $resting->fields = ['id' => $_POST['id'], 'date_end' => $_POST['date_end']];
   $resting->updateInDB(['date_end']);
   Html::back();

} else if (isset($_POST["deleterestingresources"]) && $_POST["plugin_resources_resources_id"] != 0) {
   $resting->delete($_POST, 1);
   $resting->redirectToList();

} else if (isset($_GET['menu'])) {
   if ($resting->canView() || Session::haveRight("config", UPDATE)) {
      $resting->showMenu();
   }

} else if (isset($_GET['end'])) {
   if ($resting->canView() || Session::haveRight("config", UPDATE)) {
      $resting->showFormEnd($_GET["id"], []);
   }
} else {
   if ($resting->canView() || Session::haveRight("config", UPDATE)) {
      $resting->showForm($_GET["id"], []);
   }
}

if (Session::getCurrentInterface() == 'central') {
   Html::footer();
} else {
   Html::helpFooter();
}
