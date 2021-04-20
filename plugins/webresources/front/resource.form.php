<?php

/*
 -------------------------------------------------------------------------
 Web Resources Plugin for GLPI
 Copyright (C) 2019-2020 by Curtis Conard
 https://github.com/cconard96/glpi-webresources-plugin
 -------------------------------------------------------------------------
 LICENSE
 This file is part of Web Resources Plugin for GLPI.
 Web Resources Plugin for GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 Web Resources Plugin for GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with Web Resources Plugin for GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

use Glpi\Event;

include ('../../../inc/includes.php');

$plugin = new Plugin();
if (!$plugin->isActivated('webresources')) {
   Html::displayNotFoundError();
}

if (empty($_GET["id"])) {
   $_GET["id"] = '';
}

Session::checkLoginUser();

$resource = new PluginWebresourcesResource();
if (isset($_POST["add"])) {
   $resource->check(-1, CREATE, $_POST);

   $newID = $resource->add($_POST);
   Event::log($newID, PluginWebresourcesResource::class, 4, "plugins",
      //TRANS: %1$s is the user login, %2$s is the name of the item
      sprintf(__('%1$s adds the item %2$s'), $_SESSION["glpiname"], $_POST["name"]));
   if ($_SESSION['glpibackcreated']) {
      Html::redirect($resource->getLinkURL());
   } else {
      Html::back();
   }

} else if (isset($_POST["purge"])) {
   $resource->check($_POST["id"], PURGE);
   $resource->delete($_POST, 1);

   Event::log($_POST["id"], PluginWebresourcesResource::class, 4, "plugins",
      //TRANS: %s is the user login
      sprintf(__('%s purges an item'), $_SESSION["glpiname"]));
   $resource->redirectToList();

} else if (isset($_POST["update"])) {
   $resource->check($_POST["id"], UPDATE);

   $resource->update($_POST);
   Event::log($_POST["id"], PluginWebresourcesResource::class, 4, "plugins",
      //TRANS: %s is the user login
      sprintf(__('%s updates an item'), $_SESSION["glpiname"]));

   Html::back();

} else if (isset($_POST["addvisibility"])) {
   if (isset($_POST["_type"]) && !empty($_POST["_type"])
      && isset($_POST['plugin_webresources_resources_id']) && $_POST["plugin_webresources_resources_id"]) {
      $item = null;
      switch ($_POST["_type"]) {
         case 'User' :
            if (isset($_POST['users_id']) && $_POST['users_id']) {
               $item = new PluginWebresourcesResource_User();
            }
            break;

         case 'Group' :
            if (isset($_POST['groups_id']) && $_POST['groups_id']) {
               $item = new PluginWebresourcesResource_Group();
            }
            break;

         case 'Profile' :
            if (isset($_POST['profiles_id']) && $_POST['profiles_id']) {
               $item = new PluginWebresourcesResource_Profile();
            }
            break;

         case 'Entity' :
            $item = new PluginWebresourcesResource_Entity();
            break;
      }
      if (!is_null($item)) {
         $item->add($_POST);
         Event::log($_POST["plugin_webresources_resources_id"], PluginWebresourcesResource::class, 4, "plugins",
            //TRANS: %s is the user login
            sprintf(__('%s adds a target'), $_SESSION["glpiname"]));
      }
   }
   Html::back();

} else {
   Html::header(PluginWebresourcesResource::getTypeName(Session::getPluralNumber()), '', 'plugins', 'PluginWebresourcesDashboard');
   $resource->display($_REQUEST);
   Html::footer();
}
