<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 badges plugin for GLPI
 Copyright (C) 2009-2016 by the badges Development Team.

 https://github.com/InfotelGLPI/badges
 -------------------------------------------------------------------------

 LICENSE

 This file is part of badges.

 badges is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 badges is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with badges. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include('../../../inc/includes.php');

Session::checkRight("config", UPDATE);

$config = new PluginBadgesConfig();
$notif = new PluginBadgesNotificationState();

if (isset($_POST["add"])) {

   $notif->addNotificationState($_POST['states_id']);
   Html::back();

} else if (isset($_POST["delete"])) {

   foreach ($_POST["item"] as $key => $val) {
      if ($val == 1) {
         $notif->delete(['id' => $key]);
      }
   }
   Html::back();

} else if (isset($_POST["update"])) {

   $config->update($_POST);
   Html::back();

}
