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

$budget = new PluginResourcesBudget();

if (isset($_POST["add"])) {
   $budget->check(-1, UPDATE);
   $newID = $budget->add($_POST);

   Html::back();

} else if (isset($_POST["update"])) {
   $budget->check($_POST["id"], UPDATE);
   $budget->update($_POST);

   Html::back();

} else if (isset($_POST["delete"])) {
   $budget->check($_POST["id"], UPDATE);
   $budget->delete($_POST);

   $budget->redirectToList();

} else if (isset($_POST["purge"])) {
   $budget->check($_POST['id'], UPDATE);
   $budget->delete($_POST, 1);

   $budget->redirectToList();

} else if (isset($_POST["restore"])) {
   $budget->check($_POST["id"], UPDATE);
   $budget->restore($_POST);

   $budget->redirectToList();

} else {
   $budget->checkGlobal(READ);
   Html::header(PluginResourcesResource::getTypeName(2), '', "admin", PluginResourcesMenu::getType(), strtolower(PluginResourcesBudget::getType()));
   $budget->display(['id' => $_GET["id"]]);
   Html::footer();
}
