<?php
/*
 -------------------------------------------------------------------------
 xivo plugin for GLPI
 Copyright (C) 2017 by the xivo Development Team.

 https://github.com/pluginsGLPI/xivo
 -------------------------------------------------------------------------

 LICENSE

 This file is part of xivo.

 xivo is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 xivo is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with xivo. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

$line = new PluginXivoLine();

if (isset($_POST["add"])) {
   $newID = $line->add($_POST);

   if ($_SESSION['glpibackcreated']) {
      Html::redirect($line->getFormURL()."?id=".$newID);
   }
   Html::back();

} else if (isset($_POST["delete"])) {
   $line->delete($_POST);
   $line->redirectToList();

} else if (isset($_POST["restore"])) {
   $line->restore($_POST);
   $line->redirectToList();

} else if (isset($_POST["purge"])) {
   $line->delete($_POST, 1);
   $line->redirectToList();

} else if (isset($_POST["update"])) {
   $line->update($_POST);
   Html::back();

} else {
   // fill id, if missing
   isset($_GET['id'])
      ? $ID = intval($_GET['id'])
      : $ID = 0;

   // display form
   Html::header(PluginXivoLine::getTypeName(),
             $_SERVER['PHP_SELF'],
             "management",
             "pluginxivoline");
   $line->display(['id' => $ID]);
   Html::footer();
}