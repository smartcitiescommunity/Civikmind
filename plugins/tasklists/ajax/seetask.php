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

include("../../../inc/includes.php");

Session::checkLoginUser();

Html::header_nocache();
header("Content-Type: text/html; charset=UTF-8");

//Html::requireJs('tinymce');
echo "<script type='text/javascript'  src='../../../public/lib/tinymce.js'></script>";

if (isset($_GET['id'])) {
   $options = [
      'from_edit_ajax' => true,
      'id'             => $_GET['id'],
      'withtemplate'   => 0
   ];
   echo "<div class='center'>";
   echo "<a href='" . PluginTasklistsTask::getFormURL(true) . "?id=" . $_GET['id'] . "'>" . __("View this item in his context") . "</a>";
   echo "</div>";
   echo "<hr>";
   $task = new PluginTasklistsTask();
   $task->showForm($_GET['id'],$options);
} else if (isset($_GET['plugin_tasklists_tasktypes_id'])
           && isset($_GET['plugin_tasklists_taskstates_id'])) {
   $options = [
      'from_edit_ajax'                 => true,
      'plugin_tasklists_tasktypes_id'  => $_GET['plugin_tasklists_tasktypes_id'],
      'plugin_tasklists_taskstates_id' => $_GET['plugin_tasklists_taskstates_id'],
      'withtemplate'                   => 0
   ];
   $task    = new PluginTasklistsTask();
   if ($id = $task->hasTemplate($options)) {
      $options['withtemplate'] = 2;
      $task->showForm($id, $options);
   } else {
      $task->showForm(0, $options);
   }
} else if (isset($_GET['clone_id'])) {
   $id   = $_GET['clone_id'];
   $task = new PluginTasklistsTask();
   if ($task->getFromDB($id)) {
      $options    = [
         'from_edit_ajax'                 => true,
         'plugin_tasklists_tasktypes_id'  => $task->fields['plugin_tasklists_tasktypes_id'],
         'plugin_tasklists_taskstates_id' => $task->fields['plugin_tasklists_taskstates_id'],
         'priority'                       => $task->fields['priority'],
         'users_id'                       => Session::getLoginUserID(),
         'groups_id'                      => $task->fields['groups_id'],
         'client'                         => $task->fields['client'],
         'entities_id'                    => $task->fields['entities_id'],
         'visibility'                     => $task->fields['visibility'],
         'withtemplate'                   => 0
      ];
      $taskcloned = new PluginTasklistsTask();
      $taskcloned->showForm(0, $options);
   }
} else if (isset($_GET['task_id'])) {
   $id   = $_GET['task_id'];
   $task = new PluginTasklistsTask();
   if ($task->getFromDB($id)) {
      $options = [
         'from_edit_ajax' => true,
         //'plugin_tasklists_tasktypes_id'  => $task->fields['plugin_tasklists_tasktypes_id'],
         //'plugin_tasklists_taskstates_id' => $task->fields['plugin_tasklists_taskstates_id'],
         //'priority'                       => $task->fields['priority'],
         //'users_id'                       => Session::getLoginUserID(),
         //'groups_id'                      => $task->fields['groups_id'],
         //'client'                         => $task->fields['client'],
         'entities_id'    => $task->fields['entities_id'],
         'name'           => $task->fields['name'],
         'content'        => $task->fields['comment'],
         'withtemplate'   => 0
      ];
      $ticket  = new Ticket();
      $ticket->showForm(0, $options);
   }
}
Html::ajaxFooter();