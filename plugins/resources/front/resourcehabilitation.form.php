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

include('../../../inc/includes.php');

$plugin = new Plugin();

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

$habilitation = new PluginResourcesResourceHabilitation();

if (isset($_POST['add'])) {
   if (isset($_POST['plugin_resources_habilitations_id']) &&
       !empty($_POST['plugin_resources_habilitations_id']) &&
       $_POST['plugin_resources_habilitations_id']) {
      $habilitation->check(-1, UPDATE, $_POST);
      $habilitation->add($_POST);
   }
   Html::back();

} else if (isset($_POST["delete"])) {
   $habilitation->check($_POST["id"], UPDATE);
   $habilitation->delete($_POST);
   Html::back();

}

if (Session::getCurrentInterface() == 'central') {
   Html::footer();
} else {
   Html::helpFooter();
}
