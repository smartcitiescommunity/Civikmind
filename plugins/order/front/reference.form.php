<?php
/*
 LICENSE

 This file is part of the order plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with Order. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   order
 @author    the order plugin team
 @copyright Copyright (c) 2010-2015 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/order
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

include ("../../../inc/includes.php");

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}
if (!isset($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}
if (isset($_POST["popup"])) {
   $_GET["popup"] = $_POST["popup"];
} else {
   $_GET["popup"] = "";
}
if (!isset($_GET["itemtype"])) {
   $_GET["itemtype"] = "";
}

$reference = new PluginOrderReference();

if (isset($_POST["add"])) {
   $reference->check(-1, UPDATE, $_POST);
   $newID = $reference->add($_POST);
   $url   = Toolbox::getItemTypeFormURL('PluginOrderReference')."?id=$newID";
   if (isset ($_GET["popup"]) && $_GET["popup"] == 1) {
      $url .= "&popup=1";
   }
   if ($_SESSION['glpibackcreated']) {
      Html::redirect($reference->getFormURL()."?id=".$newID);
   } else {
      Html::redirect($url);
   }

   /* delete order */
} else if (isset($_POST["delete"])) {
   $reference->check($_POST['id'], UPDATE);
   $reference->delete($_POST);
   $reference->redirectToList();

   /* restore order */
} else if (isset($_POST["restore"])) {
   $reference->check($_POST['id'], UPDATE);
   $reference->restore($_POST);
   $reference->redirectToList();

   /* purge order */
} else if (isset($_POST["purge"])) {
    $reference->check($_POST['id'], UPDATE);
   $reference->delete($_POST, 1);
    $reference->redirectToList();

   /* update order */
} else if (isset($_POST["update"])) {
   $reference->check($_POST['id'], UPDATE);
   $reference->update($_POST);
   Html::back();
}

if (isset($_GET["popup"]) && $_GET["popup"] == 1) {
   Html::popheader(
      PluginOrderReference::getTypeName(1),
      $_SERVER['PHP_SELF'],
      "management",
      "PluginOrderMenu",
      "references"
   );
} else {
   Html::header(
      PluginOrderReference::getTypeName(1),
      $_SERVER['PHP_SELF'],
      "management",
      "PluginOrderMenu",
      "references"
   );
}

if ($_GET['id'] == "") {
   $reference->showForm(-1);
} else {
   $reference->display($_GET, [
      'withtemplate' => $_GET['withtemplate'],
      'popup'        => $_GET["popup"],
      'item'         => $_GET["itemtype"],
   ]);
}

if (isset ($_GET["popup"]) && $_GET["popup"] == 1) {
   Html::popfooter();
} else {
   Html::footer();
}
