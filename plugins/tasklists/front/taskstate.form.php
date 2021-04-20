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


include('../../../inc/includes.php');

Session::checkRight("plugin_tasklists", READ);

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}
if (!isset($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}

$task = new PluginTasklistsTaskState();

if (isset($_POST["add"])) {
   $task->check(-1, CREATE, $_POST);
   $newID = $task->add($_POST);
   if (!isset($_POST["from_edit_ajax"])) {
      Html::redirect($task->getFormURL() . "?id=" . $newID);
   } else {
      Html::back();
   }

} else if (isset($_POST["delete"])) {
   $task->check($_POST['id'], DELETE);
   $task->delete($_POST);
   if (!isset($_POST["from_edit_ajax"])) {
      $task->redirectToList();
   } else {
      Html::back();
   }

} else if (isset($_POST["restore"])) {
   $task->check($_POST['id'], PURGE);
   $task->restore($_POST);
   $task->redirectToList();

} else if (isset($_POST["purge"])) {
   $task->check($_POST['id'], PURGE);
   $task->delete($_POST, 1);
   if (!isset($_POST["from_edit_ajax"])) {
      $task->redirectToList();
   } else {
      Html::back();
   }

} else if (isset($_POST["update"])) {
   $task->check($_POST['id'], UPDATE);
   $task->update($_POST);
   Html::back();

} else if (isset($_POST["done"])) {
   $task->check($_POST['id'], UPDATE);
   $options['id']           = $_POST['id'];
   $options['state']        = 2;
   $options['percent_done'] = 100;
   $task->update($options);
   Html::back();

} else if (isset($_POST["ticket_link"])) {

   $ticket = new PluginTasklistsTicket();
   $task   = new PluginTasklistsTask();
   $task->check($_POST['plugin_tasklists_tasks_id'], UPDATE);
   $ticket->add(['tickets_id'                => $_POST['tickets_id'],
                 'plugin_tasklists_tasks_id' => $_POST['plugin_tasklists_tasks_id']]);
   Html::back();

} else {

   $task->checkGlobal(READ);

   Html::header(PluginTasklistsTask::getTypeName(2), '', "helpdesk", "plugintasklistsmenu");

   Html::requireJs('tinymce');
   $task->display(['id' => $_GET["id"], 'withtemplate' => $_GET["withtemplate"]]);

   Html::footer();
}
