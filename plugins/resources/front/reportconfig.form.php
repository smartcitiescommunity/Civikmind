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

if (!isset($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}

if (!isset($_GET["plugin_resources_resources_id"])) {
   $_GET["plugin_resources_resources_id"] = "";
}

$reportconfig = new PluginResourcesReportConfig();

if (isset($_POST["add"])) {
   if ($reportconfig->canCreate()) {
      $reportconfig->add($_POST);
   }
   Html::back();

} else if (isset($_POST["update"])) {
   if ($reportconfig->canCreate()) {
      $reportconfig->update($_POST);
   }
   Html::back();

} else if (isset($_POST["delete"])) {
   if ($reportconfig->canCreate()) {
      $reportconfig->delete($_POST, 1);
   }

   Html::redirect(Toolbox::getItemTypeFormURL('PluginResourcesResource').
           "?id=".$_POST["plugin_resources_resources_id"]);

} else if (isset($_POST["delete_report"])) {
   if ($reportconfig->canCreate()) {
      foreach ($_POST["check"] as $ID => $value) {
         $reportconfig->delete(["id" => $ID], 1);
      }
   }
   Html::back();

} else {
   $reportconfig->checkGlobal(READ);
   Html::header(PluginResourcesResource::getTypeName(2), '', "admin", PluginResourcesMenu::getType());
   $reportconfig->display(['id' => $_GET["id"], 'plugin_resources_resources_id' => $_GET["plugin_resources_resources_id"]]);
   Html::footer();
}
