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

$resource_change = new PluginResourcesResource_Change();

if (isset($_POST['load_button_changeresources'])) {
   $resource_change->loadButtonChangeResources($_POST['action'], $_POST);
} else if (isset($_POST['action'])) {

   switch ($_POST['action']) {
      case "loadEntity" :
         $resource_change->loadEntity($_POST['actions_id']);
         break;
      case "loadCategory" :
         $resource_change->displayCategory($_POST['entities_id']);
         break;
      case "loadButtonAdd" :
         $resource_change->displayButtonAdd($_POST['itilcategories_id']);
         break;
      case "clean" :
         echo "";
         break;

   }

} else {
   $resource_change->setFieldByAction($_POST["id"], $_POST['plugin_resources_resources_id']);
}
