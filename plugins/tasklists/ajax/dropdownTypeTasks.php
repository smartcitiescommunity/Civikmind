<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Tasklists plugin for GLPI
 Copyright (C) 2003-2016 by the Tasklists Development Team.

 https://github.com/InfotelGLPI/tasklists
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Tasklists.

 Tasklists is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Tasklists is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Tasklists. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (strpos($_SERVER['PHP_SELF'], "dropdownTypeTasks.php")) {
   $AJAX_INCLUDE = 1;
   include('../../../inc/includes.php');
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

Session::checkCentralAccess();

global $DB;

// Make a select box
if (isset($_POST["tasktypes"])) {
   $used = [];

   // Clean used array
   if (isset($_POST['used']) && is_array($_POST['used']) && (count($_POST['used']) > 0)) {
      $query = "SELECT `id`
                FROM `glpi_plugin_tasklists_tasks`
                WHERE `id` IN (" . implode(',', $_POST['used']) . ")
                      AND `plugin_tasklists_tasktypes_id` = '" . $_POST["tasktypes"] . "'";

      foreach ($DB->request($query) AS $data) {
         $used[$data['id']] = $data['id'];
      }
   }

   Dropdown::show('PluginTasklistsTask',
                  ['name'      => $_POST['myname'],
                   'used'      => $used,
                   'width'     => '50%',
                   'entity'    => $_POST['entity'],
                   'rand'      => $_POST['rand'],
                   'condition' => ["glpi_plugin_tasklists_tasks.plugin_tasklists_tasktypes_id" => $_POST["tasktypes"]]]);
}
