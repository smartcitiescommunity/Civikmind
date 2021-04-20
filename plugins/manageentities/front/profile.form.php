<?php
/*
 -------------------------------------------------------------------------
 Manageentities plugin for GLPI
 Copyright (C) 2003-2012 by the Manageentities Development Team.

 https://forge.indepnet.net/projects/manageentities
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
// */

define('GLPI_ROOT', '../../..');
include(GLPI_ROOT . "/inc/includes.php");

Session::checkRight("profile", "r");

$prof = new PluginManageentitiesProfile();

//Save profile
if (isset ($_POST['update'])) {
   $prof->update($_POST);
   Html::back();
}

?>