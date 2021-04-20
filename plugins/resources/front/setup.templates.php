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

$resource = new PluginResourcesResource();

if ($resource->canView() || Session::haveRight("config", UPDATE)) {
   if (Session::getCurrentInterface() == 'central') {
      Html::header(PluginResourcesResource::getTypeName(2), '', "admin", PluginResourcesMenu::getType());
   } else {
      Html::helpHeader(PluginResourcesResource::getTypeName(2));
   }

   $resource->listOfTemplates($CFG_GLPI["root_doc"]."/plugins/resources/front/resource.form.php", $_GET["add"]);

   if (Session::getCurrentInterface() == 'central') {
      Html::footer();
   } else {
      Html::helpFooter();
   }
}
