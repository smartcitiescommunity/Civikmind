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
   Html::header(PluginResourcesResource::getTypeName(2), '', "admin", PluginResourcesMenu::getType());
} else {
   Html::helpHeader(PluginResourcesResource::getTypeName(2));
}

$resource = new PluginResourcesResource();

if (isset($_POST["transferresources"])) {
   if ($resource->checkTransferMandatoryFields($_POST)) {
      $resource->transferResource($_POST["plugin_resources_resources_id"], $_POST['entities_id'], $_POST);
      Html::redirect($CFG_GLPI['root_doc']."/plugins/resources/front/resource.change.php");

   } else {
      Html::back();
   }

} else {
   if ($resource->canView() || Session::haveRight("config", "w")) {
      //show remove resource form
      $resource->showResourcesToTransfer($_GET['plugin_resources_resources_id']);
   }
}

if (Session::getCurrentInterface() == 'central') {
   Html::footer();
} else {
   Html::helpFooter();
}
