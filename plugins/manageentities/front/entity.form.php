<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Manageentities plugin for GLPI
 Copyright (C) 2014-2017 by the Manageentities Development Team.

 https://github.com/InfotelGLPI/manageentities
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Manageentities.

 Manageentities is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Manageentities is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Manageentities. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include('../../../inc/includes.php');

$logo = new PluginManageentitiesEntityLogo();


if (isset($_POST["add"])) {

   if (isset($_POST["_filename"]) && count($_POST["_filename"]) > 0) {
      $logo->addLogo($_POST);
   } else {
      Session::addMessageAfterRedirect(__('No picture uploaded', 'manageentities'), false, ERROR);
   }

   Html::back();

} else if (isset($_POST["update"])
           && isset($_POST["entities_id"])) {

   Html::redirect($CFG_GLPI["root_doc"] . "/front/entity.form.php?id=" . $_POST["entities_id"] . "&amps&forcetab=EntityData$1");

}