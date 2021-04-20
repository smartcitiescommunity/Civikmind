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

Html::header_nocache();
Session::checkLoginUser();
header("Content-Type: text/html; charset=UTF-8");

if (isset($_POST['action'])) {
   switch ($_POST['action']) {
      case "load" :
         if (Session::haveRight("task", CommonITILTask::UPDATEALL)
             && Session::haveRight("task", CommonITILTask::ADDALLITEM)
             && strpos($_SERVER['HTTP_REFERER'], "ticket.form.php") !== false
             && strpos($_SERVER['HTTP_REFERER'], 'id=') !== false
             && Session::getCurrentInterface() == "central"
             && Session::haveRight("plugin_manageentities", READ)) {

            echo "<script type='text/javascript'>showCloneTicketTask(" . json_encode(['root_doc' => $CFG_GLPI['root_doc']]) . ");</script>";
         }
         break;
   }
}