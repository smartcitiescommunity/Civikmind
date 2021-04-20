<?php
/*
 *
 -------------------------------------------------------------------------
 Plugin GLPI News
 Copyright (C) 2015 by teclib.
 http://www.teclib.com
 -------------------------------------------------------------------------
 LICENSE
 This file is part of Plugin GLPI News.
 Plugin GLPI News is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 Plugin GLPI News is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with Plugin GLPI News. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

use Glpi\Event;

include ("../../../inc/includes.php");

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}

$alert = new PluginNewsAlert();

if (isset($_POST['update'])) {
   $alert->check($_POST['id'], UPDATE);
   if ($alert->update($_POST)) {
      Event::log($_POST["id"], "PluginNewsAlert", 4, "admin",
              sprintf(__('%s updates an item'), $_SESSION["glpiname"]));
   }
   Html::back();

} else if (isset($_POST['add'])) {
   $alert->check(-1, CREATE, $_POST);
   if ($newID = $alert->add($_POST)) {
      Event::log($newID, "PluginNewsAlert", 4, "admin",
                 sprintf(__('%1$s adds the item %2$s'), $_SESSION["glpiname"], $_POST["name"]));

      if ($_SESSION['glpibackcreated']) {
         Html::redirect($alert->getFormURL()."?id=".$newID);
      }
   }
   Html::back();

} else if (isset($_POST['delete'])) {
   $alert->check($_POST['id'], DELETE);
   if ($alert->delete($_POST)) {
      Event::log($_POST["id"], "PluginNewsAlert", 4, "admin",
                 sprintf(__('%s deletes an item'), $_SESSION["glpiname"]));
   }
   $alert->redirectToList();

} else if (isset($_POST['restore'])) {
   $alert->check($_POST['id'], DELETE);
   if ($alert->restore($_POST)) {
      Event::log($_POST["id"], "PluginNewsAlert", 4, "admin",
                 sprintf(__('%s restores an item'), $_SESSION["glpiname"]));
   }
   Html::back();

} else if (isset($_POST['purge'])) {
   $alert->check($_POST['id'], PURGE);
   if ($alert->delete($_POST, 1)) {
      Event::log($_POST["id"], "PluginNewsAlert", 4, "admin",
                 sprintf(__('%s purges an item'), $_SESSION["glpiname"]));
   }
   $alert->redirectToList();

} else if (isset($_POST["addvisibility"])) {
   $alert->check($_POST['plugin_news_alerts_id'], UPDATE);
   $target = new PluginNewsAlert_Target();
   $target->add($_POST);
   Html::back();
}

Html::header(
   __('Alerts', 'news'),
   $_SERVER["PHP_SELF"],
   'tools',
   "PluginNewsAlert"
);

$alert->display(['id'=> $_GET["id"]]);

Html::footer();
